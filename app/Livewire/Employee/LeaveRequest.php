<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LeaveRequest as LeaveRequestModel;
use Carbon\Carbon;

class LeaveRequest extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editing = false;
    public $leaveRequestId;

    // Form fields
    public $leave_type = '';
    public $start_date = '';
    public $end_date = '';
    public $days_requested = 0;
    public $reason = '';

    protected function rules()
    {
        return [
            'leave_type' => 'required|in:vacation,sick,personal,other',
            'start_date' => 'required|date|after_or_equal:' . now()->format('Y-m-d'),
            'end_date' => 'required|date|after_or_equal:start_date',
            'days_requested' => 'required|integer|min:1|max:30',
            'reason' => 'required|string|max:500',
        ];
    }

    public function mount()
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
    }

    public function openModal()
    {
        $this->showModal = true;
        $this->editing = false;
        $this->resetForm();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editing = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->leave_type = '';
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
        $this->days_requested = 0;
        $this->reason = '';
    }

    public function updatedStartDate()
    {
        if ($this->start_date && $this->end_date) {
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->end_date);
            $this->days_requested = $start->diffInDays($end) + 1;
        }
    }

    public function updatedEndDate()
    {
        if ($this->start_date && $this->end_date) {
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->end_date);
            $this->days_requested = $start->diffInDays($end) + 1;
        }
    }

    public function save()
    {
        $this->validate();

        LeaveRequestModel::create([
            'user_id' => auth()->user()->id,
            'leave_type' => $this->leave_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'days_requested' => $this->days_requested,
            'reason' => $this->reason,
            'status' => 'pending',
        ]);

        session()->flash('message', 'Leave request submitted successfully!');
        $this->closeModal();
    }

    public function cancelRequest($id)
    {
        $leaveRequest = LeaveRequestModel::where('id', $id)
            ->where('user_id', auth()->user()->id)
            ->where('status', 'pending')
            ->first();

        if ($leaveRequest) {
            $leaveRequest->delete();
            session()->flash('message', 'Leave request cancelled successfully!');
        } else {
            session()->flash('error', 'Cannot cancel this leave request.');
        }
    }

    public function getLeaveRequestsProperty()
    {
        return LeaveRequestModel::where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getLeaveStatsProperty()
    {
        $totalRequests = LeaveRequestModel::where('user_id', auth()->user()->id)->count();
        $pendingRequests = LeaveRequestModel::where('user_id', auth()->user()->id)
            ->where('status', 'pending')->count();
        $approvedRequests = LeaveRequestModel::where('user_id', auth()->user()->id)
            ->where('status', 'approved')->count();
        $rejectedRequests = LeaveRequestModel::where('user_id', auth()->user()->id)
            ->where('status', 'rejected')->count();

        return [
            'total' => $totalRequests,
            'pending' => $pendingRequests,
            'approved' => $approvedRequests,
            'rejected' => $rejectedRequests,
        ];
    }

    public function render()
    {
        return view('livewire.employee.leave-request')
            ->layout('layouts.app');
    }
} 