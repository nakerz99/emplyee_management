<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\LeaveRequest;
use App\Models\User;
use Livewire\WithPagination;
use Carbon\Carbon;

class LeaveRequestManagement extends Component
{
    use WithPagination;

    public $showModal = false;
    public $selectedRequest = null;
    public $adminNotes = '';
    public $action = ''; // 'approve' or 'reject'

    // Filters
    public $search = '';
    public $selectedEmployee = '';
    public $selectedDepartment = '';
    public $selectedStatus = '';
    public $selectedLeaveType = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $rules = [
        'adminNotes' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        // Don't set default date range to avoid filtering out test data
        $this->dateFrom = '';
        $this->dateTo = '';
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

    public function updatedSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatedSelectedLeaveType()
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

    public function openApprovalModal($requestId, $action)
    {
        $this->selectedRequest = LeaveRequest::with('user')->findOrFail($requestId);
        $this->action = $action;
        $this->adminNotes = '';
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedRequest = null;
        $this->adminNotes = '';
        $this->action = '';
        $this->resetValidation();
    }

    public function approveRequest()
    {
        $this->validate();

        if ($this->selectedRequest && $this->action === 'approve') {
            $this->selectedRequest->approve(auth()->user(), $this->adminNotes);
            session()->flash('message', 'Leave request approved successfully!');
        }

        $this->closeModal();
    }

    public function rejectRequest()
    {
        $this->validate();

        if ($this->selectedRequest && $this->action === 'reject') {
            $this->selectedRequest->reject(auth()->user(), $this->adminNotes);
            session()->flash('message', 'Leave request rejected successfully!');
        }

        $this->closeModal();
    }

    public function bulkApprove()
    {
        $pendingRequests = LeaveRequest::where('status', 'pending')->get();
        
        foreach ($pendingRequests as $request) {
            $request->approve(auth()->user(), 'Bulk approved by admin');
        }

        session()->flash('message', count($pendingRequests) . ' leave requests approved successfully!');
    }

    public function getLeaveRequestsProperty()
    {
        $query = LeaveRequest::with(['user', 'approvedBy'])
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
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->when($this->selectedLeaveType, function ($query) {
                $query->where('leave_type', $this->selectedLeaveType);
            })
            ->when($this->dateFrom, function ($query) {
                $query->where('start_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->where('end_date', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc');

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
        $query = LeaveRequest::query()
            ->when($this->selectedEmployee, function ($query) {
                $query->where('user_id', $this->selectedEmployee);
            })
            ->when($this->selectedDepartment, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('department', $this->selectedDepartment);
                });
            })
            ->when($this->dateFrom, function ($query) {
                $query->where('start_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->where('end_date', '<=', $this->dateTo);
            });

        return [
            'total_requests' => $query->count(),
            'pending_requests' => $query->where('status', 'pending')->count(),
            'approved_requests' => $query->where('status', 'approved')->count(),
            'rejected_requests' => $query->where('status', 'rejected')->count(),
            'total_days' => $query->sum('days_requested'),
        ];
    }

    public function render()
    {
        return view('livewire.admin.leave-request-management')
            ->layout('layouts.app');
    }
}
