<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\TimeRecord;
use App\Models\BreakSession;
use Livewire\WithPagination;
use Carbon\Carbon;

class TimeRecordManagement extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editing = false;
    public $timeRecordId = null;
    
    // Form fields
    public $user_id = '';
    public $date = '';
    public $clock_in = '';
    public $clock_out = '';
    public $total_hours = 0;
    public $overtime_hours = 0;
    public $status = 'active';

    // Filters
    public $search = '';
    public $selectedEmployee = '';
    public $selectedDepartment = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $selectedStatus = '';

    // Bulk operations
    public $selectedRecords = [];
    public $selectAll = false;

    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'date' => 'required|date',
        'clock_in' => 'required|date_format:H:i',
        'clock_out' => 'nullable|date_format:H:i|after:clock_in',
        'total_hours' => 'nullable|numeric|min:0',
        'overtime_hours' => 'nullable|numeric|min:0',
        'status' => 'required|in:active,inactive',
    ];

    protected $messages = [
        'user_id.required' => 'Employee is required.',
        'user_id.exists' => 'Selected employee does not exist.',
        'date.required' => 'Date is required.',
        'date.date' => 'Please enter a valid date.',
        'clock_in.required' => 'Clock in time is required.',
        'clock_in.date_format' => 'Please enter a valid time format (HH:MM).',
        'clock_out.date_format' => 'Please enter a valid time format (HH:MM).',
        'clock_out.after' => 'Clock out time must be after clock in time.',
        'total_hours.numeric' => 'Total hours must be a number.',
        'total_hours.min' => 'Total hours cannot be negative.',
        'overtime_hours.numeric' => 'Overtime hours must be a number.',
        'overtime_hours.min' => 'Overtime hours cannot be negative.',
    ];

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedEmployee()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
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
            $this->selectedRecords = $this->timeRecords->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selectedRecords = [];
        }
    }

    public function openModal($timeRecordId = null)
    {
        $this->resetForm();
        
        if ($timeRecordId) {
            $this->editing = true;
            $this->timeRecordId = $timeRecordId;
            $this->loadTimeRecord($timeRecordId);
        } else {
            $this->editing = false;
            $this->timeRecordId = null;
            $this->date = now()->format('Y-m-d');
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function loadTimeRecord($timeRecordId)
    {
        $timeRecord = TimeRecord::findOrFail($timeRecordId);
        
        $this->user_id = $timeRecord->user_id;
        $this->date = $timeRecord->date->format('Y-m-d');
        $this->clock_in = $timeRecord->clock_in ? Carbon::parse($timeRecord->clock_in)->format('H:i') : '';
        $this->clock_out = $timeRecord->clock_out ? Carbon::parse($timeRecord->clock_out)->format('H:i') : '';
        $this->total_hours = $timeRecord->total_hours ?? 0;
        $this->overtime_hours = $timeRecord->overtime_hours ?? 0;
        $this->status = $timeRecord->status ?? 'active';
    }

    public function resetForm()
    {
        $this->user_id = '';
        $this->date = '';
        $this->clock_in = '';
        $this->clock_out = '';
        $this->total_hours = 0;
        $this->overtime_hours = 0;
        $this->status = 'active';
        
        $this->resetValidation();
    }

    public function save()
    {
        if ($this->editing) {
            $this->rules['user_id'] = 'required|exists:users,id';
        }

        $this->validate();

        $timeRecordData = [
            'user_id' => $this->user_id,
            'date' => $this->date,
            'clock_in' => $this->date . ' ' . $this->clock_in . ':00',
            'clock_out' => $this->clock_out ? $this->date . ' ' . $this->clock_out . ':00' : null,
            'total_hours' => $this->total_hours,
            'overtime_hours' => $this->overtime_hours,
            'status' => $this->status,
        ];

        if ($this->editing) {
            $timeRecord = TimeRecord::findOrFail($this->timeRecordId);
            $timeRecord->update($timeRecordData);
            session()->flash('message', 'Time record updated successfully!');
        } else {
            // Use the helper method to prevent duplicate records for the same user on the same date
            TimeRecord::getOrCreateForUserOnDate(
                $this->user_id,
                $this->date,
                $timeRecordData
            );
            session()->flash('message', 'Time record created successfully!');
        }

        $this->closeModal();
    }

    public function deleteTimeRecord($timeRecordId)
    {
        $timeRecord = TimeRecord::findOrFail($timeRecordId);
        $timeRecord->delete();
        session()->flash('message', 'Time record deleted successfully!');
    }

    public function bulkDelete()
    {
        if (empty($this->selectedRecords)) {
            session()->flash('error', 'Please select records to delete.');
            return;
        }

        TimeRecord::whereIn('id', $this->selectedRecords)->delete();
        $this->selectedRecords = [];
        $this->selectAll = false;
        session()->flash('message', count($this->selectedRecords) . ' time records deleted successfully!');
    }

    public function bulkExport()
    {
        if (empty($this->selectedRecords)) {
            session()->flash('error', 'Please select records to export.');
            return;
        }

        // In a real implementation, you'd generate an Excel file
        session()->flash('message', 'Export functionality will be implemented soon!');
    }

    public function getTimeRecordsProperty()
    {
        $query = TimeRecord::with(['user'])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedEmployee, function ($query) {
                $query->where('user_id', $this->selectedEmployee);
            })
            ->when($this->selectedDepartment, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('department', $this->selectedDepartment);
                });
            })
            ->when($this->dateFrom, function ($query) {
                $query->where('date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->where('date', '<=', $this->dateTo);
            })
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->orderBy('date', 'desc')
            ->orderBy('clock_in', 'desc');

        return $query->paginate(15);
    }

    public function getEmployeesProperty()
    {
        return User::where('role', 'employee')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getDepartmentsProperty()
    {
        return User::where('role', 'employee')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->filter();
    }

    public function getSummaryStatsProperty()
    {
        $query = TimeRecord::query()
            ->when($this->selectedEmployee, function ($query) {
                $query->where('user_id', $this->selectedEmployee);
            })
            ->when($this->selectedDepartment, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('department', $this->selectedDepartment);
                });
            })
            ->when($this->dateFrom, function ($query) {
                $query->where('date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->where('date', '<=', $this->dateTo);
            });

        return [
            'total_records' => $query->count(),
            'total_hours' => $query->sum('total_hours'),
            'total_overtime' => $query->sum('overtime_hours'),
            'average_hours' => $query->count() > 0 ? round($query->sum('total_hours') / $query->count(), 2) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.admin.time-record-management')
            ->layout('layouts.app');
    }
}
