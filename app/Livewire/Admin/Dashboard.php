<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\TimeRecord;
use App\Models\Announcement;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public $totalEmployees;
    public $activeEmployees;
    public $employeesOnLeave;
    public $monthlySpending;
    public $birthdayCelebrants;
    public $pendingLeaveRequests;

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $this->totalEmployees = User::where('role', 'employee')->count();
        $this->activeEmployees = User::where('role', 'employee')->where('status', 'active')->count();
        $this->employeesOnLeave = User::where('role', 'employee')->where('status', 'on_leave')->count();
        
        $this->calculateSpending();
        
        $this->birthdayCelebrants = User::getBirthdayCelebrants();
        $this->pendingLeaveRequests = LeaveRequest::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function calculateSpending()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $this->monthlySpending = User::where('role', 'employee')
            ->where('status', 'active')
            ->get()
            ->sum(function ($user) use ($currentMonth, $currentYear) {
                return $user->getTotalPayForMonth($currentMonth, $currentYear);
            });
    }

    public function getActiveTimeRecordsProperty()
    {
        return TimeRecord::with('user')
            ->where('date', today())
            ->where('status', 'active')
            ->orderBy('clock_in', 'desc')
            ->paginate(5);
    }

    public function getDepartmentSpendingProperty()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $departments = User::where('role', 'employee')
            ->where('status', 'active')
            ->selectRaw('department, SUM(hourly_rate * (
                SELECT COALESCE(SUM(total_hours), 0) 
                FROM time_records 
                WHERE user_id = users.id 
                AND MONTH(date) = ? 
                AND YEAR(date) = ?
            )) as total_spending', [$currentMonth, $currentYear])
            ->groupBy('department')
            ->orderBy('total_spending', 'desc')
            ->get();

        // Manually paginate the collection
        $perPage = 5;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $departments->slice($offset, $perPage);
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $departments->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    public function getRecentAnnouncementsProperty()
    {
        return Announcement::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(5);
    }

    public function render()
    {
        return view('livewire.admin.dashboard')
            ->layout('layouts.app');
    }
}
