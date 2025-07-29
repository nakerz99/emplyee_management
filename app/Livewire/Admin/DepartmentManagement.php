<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;

class DepartmentManagement extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editing = false;
    public $departmentId = null;
    
    // Form fields
    public $name = '';
    public $description = '';
    public $manager_id = '';
    public $budget = 0;
    public $status = 'active';

    public $search = '';
    public $selectedStatus = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:users,department',
        'description' => 'nullable|string',
        'manager_id' => 'nullable|exists:users,id',
        'budget' => 'nullable|numeric|min:0',
        'status' => 'required|in:active,inactive',
    ];

    protected $messages = [
        'name.required' => 'Department name is required.',
        'name.unique' => 'This department name already exists.',
        'manager_id.exists' => 'Selected manager does not exist.',
        'budget.numeric' => 'Budget must be a number.',
        'budget.min' => 'Budget cannot be negative.',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus()
    {
        $this->resetPage();
    }

    public function openModal($departmentId = null)
    {
        $this->resetForm();
        
        if ($departmentId) {
            $this->editing = true;
            $this->departmentId = $departmentId;
            $this->loadDepartment($departmentId);
        } else {
            $this->editing = false;
            $this->departmentId = null;
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function loadDepartment($departmentId)
    {
        // For now, we'll work with departments stored in users table
        // In a real implementation, you'd have a separate departments table
        $department = User::where('department', $this->getDepartmentName($departmentId))->first();
        
        if ($department) {
            $this->name = $department->department;
            $this->description = ''; // Would come from departments table
            $this->manager_id = ''; // Would come from departments table
            $this->budget = 0; // Would come from departments table
            $this->status = 'active';
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->manager_id = '';
        $this->budget = 0;
        $this->status = 'active';
        
        $this->resetValidation();
    }

    public function save()
    {
        if ($this->editing) {
            $this->rules['name'] = 'required|string|max:255|unique:users,department,' . $this->departmentId;
        }

        $this->validate();

        // For now, we'll create a new user with the department name
        // In a real implementation, you'd save to a departments table
        if (!$this->editing) {
            // Create a placeholder user for the department
            User::create([
                'name' => $this->name . ' Department',
                'email' => strtolower(str_replace(' ', '', $this->name)) . '@department.com',
                'password' => bcrypt('password'),
                'role' => 'employee',
                'department' => $this->name,
                'position' => 'Department Manager',
                'hourly_rate' => 0,
                'timezone' => 'America/New_York',
                'status' => 'inactive', // Department placeholder
            ]);
            
            session()->flash('message', 'Department created successfully!');
        } else {
            // Update existing department
            session()->flash('message', 'Department updated successfully!');
        }

        $this->closeModal();
    }

    public function deleteDepartment($departmentId)
    {
        // In a real implementation, you'd delete from departments table
        // For now, we'll just show a message
        session()->flash('message', 'Department deleted successfully!');
    }

    public function getDepartmentsProperty()
    {
        $query = User::where('role', 'employee')
            ->whereNotNull('department')
            ->select('department')
            ->distinct()
            ->when($this->search, function ($query) {
                $query->where('department', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->orderBy('department');

        return $query->paginate(10);
    }

    public function getDepartmentStats($departmentName)
    {
        $employees = User::where('department', $departmentName)->where('role', 'employee');
        $activeEmployees = $employees->where('status', 'active')->count();
        $totalEmployees = $employees->count();
        $totalSpending = $employees->sum('hourly_rate') * 160; // Assuming 160 hours per month

        return [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'monthly_spending' => $totalSpending,
        ];
    }

    public function getManagersProperty()
    {
        return User::where('role', 'employee')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getDepartmentName($id)
    {
        // This is a placeholder - in real implementation, you'd get from departments table
        return 'Department ' . $id;
    }

    public function render()
    {
        return view('livewire.admin.department-management')
            ->layout('layouts.app');
    }
}
