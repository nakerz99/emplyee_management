<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\WithPagination;

class EmployeeManagement extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editing = false;
    public $userId = null;
    
    // Form fields
    public $name = '';
    public $email = '';
    public $password = '';
    public $role = 'employee';
    public $position = '';
    public $department = '';
    public $hourly_rate = 0;
    public $timezone = 'America/New_York';
    public $status = 'active';
    public $birthday = '';
    public $phone = '';
    public $address = '';

    public $search = '';
    public $selectedDepartment = '';
    public $selectedStatus = '';

    // Bulk operations
    public $selectedEmployees = [];
    public $selectAll = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'role' => 'required|in:admin,employee',
        'position' => 'nullable|string|max:255',
        'department' => 'nullable|string|max:255',
        'hourly_rate' => 'required|numeric|min:0',
        'timezone' => 'required|string',
        'status' => 'required|in:active,inactive,on_leave',
        'birthday' => 'nullable|date',
        'phone' => 'nullable|string|max:255',
        'address' => 'nullable|string',
    ];

    protected $messages = [
        'name.required' => 'Name is required.',
        'email.required' => 'Email is required.',
        'email.email' => 'Please enter a valid email address.',
        'email.unique' => 'This email is already registered.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 6 characters.',
        'hourly_rate.required' => 'Hourly rate is required.',
        'hourly_rate.numeric' => 'Hourly rate must be a number.',
        'hourly_rate.min' => 'Hourly rate cannot be negative.',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedEmployees = $this->employees->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selectedEmployees = [];
        }
    }

    public function openModal($userId = null)
    {
        $this->resetForm();
        
        if ($userId) {
            $this->editing = true;
            $this->userId = $userId;
            $this->loadUser($userId);
        } else {
            $this->editing = false;
            $this->userId = null;
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function loadUser($userId)
    {
        $user = User::findOrFail($userId);
        
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->position = $user->position ?? '';
        $this->department = $user->department ?? '';
        $this->hourly_rate = $user->hourly_rate ?? 0;
        $this->timezone = $user->timezone ?? 'America/New_York';
        $this->status = $user->status ?? 'active';
        $this->birthday = $user->birthday ? $user->birthday->format('Y-m-d') : '';
        $this->phone = $user->phone ?? '';
        $this->address = $user->address ?? '';
        
        // Don't load password for editing
        $this->password = '';
    }

    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'employee';
        $this->position = '';
        $this->department = '';
        $this->hourly_rate = 0;
        $this->timezone = 'America/New_York';
        $this->status = 'active';
        $this->birthday = '';
        $this->phone = '';
        $this->address = '';
        
        $this->resetValidation();
    }

    public function save()
    {
        if ($this->editing) {
            $this->rules['email'] = 'required|email|unique:users,email,' . $this->userId;
            $this->rules['password'] = 'nullable|min:6';
        }

        $this->validate();

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'position' => $this->position,
            'department' => $this->department,
            'hourly_rate' => $this->hourly_rate,
            'timezone' => $this->timezone,
            'status' => $this->status,
            'birthday' => $this->birthday ?: null,
            'phone' => $this->phone,
            'address' => $this->address,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        if ($this->editing) {
            $user = User::findOrFail($this->userId);
            $user->update($userData);
            session()->flash('message', 'Employee updated successfully!');
        } else {
            User::create($userData);
            session()->flash('message', 'Employee created successfully!');
        }

        $this->closeModal();
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        
        // Don't allow deleting the current admin user
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account!');
            return;
        }
        
        $user->delete();
        session()->flash('message', 'Employee deleted successfully!');
    }

    public function bulkDelete()
    {
        if (empty($this->selectedEmployees)) {
            session()->flash('error', 'Please select employees to delete.');
            return;
        }

        User::whereIn('id', $this->selectedEmployees)->delete();
        $this->selectedEmployees = [];
        $this->selectAll = false;
        session()->flash('message', count($this->selectedEmployees) . ' employees deleted successfully!');
    }

    public function bulkExport()
    {
        if (empty($this->selectedEmployees)) {
            session()->flash('error', 'Please select employees to export.');
            return;
        }

        // In a real implementation, you'd generate an Excel file
        session()->flash('message', 'Export functionality will be implemented soon!');
    }

    public function bulkStatusUpdate($status)
    {
        if (empty($this->selectedEmployees)) {
            session()->flash('error', 'Please select employees to update.');
            return;
        }

        User::whereIn('id', $this->selectedEmployees)->update(['status' => $status]);
        $this->selectedEmployees = [];
        $this->selectAll = false;
        session()->flash('message', count($this->selectedEmployees) . ' employees status updated successfully!');
    }

    public function getEmployeesProperty()
    {
        $query = User::where('role', 'employee')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('position', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedDepartment, function ($query) {
                $query->where('department', $this->selectedDepartment);
            })
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->orderBy('name');

        return $query->paginate(10);
    }

    public function getDepartmentsProperty()
    {
        return User::where('role', 'employee')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->filter();
    }

    public function render()
    {
        return view('livewire.admin.employee-management')
            ->layout('layouts.app');
    }
}
