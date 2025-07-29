<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use App\Models\TimeRecord;
use App\Models\Announcement;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $user;
    public $todayRecord;
    public $isClockedIn = false;
    public $isOnBreak = false;
    public $currentTime;
    public $totalHoursToday = 0;
    public $totalHoursThisMonth = 0;
    public $recentAnnouncements;
    public $myLeaveRequests;
    public $clockOutNote = ''; // Add note field for clock out

    public function mount()
    {
        $this->user = auth()->user();
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $this->todayRecord = $this->user->todayTimeRecord();
        $this->isClockedIn = $this->user->isClockedIn();
        $this->isOnBreak = $this->user->isOnBreak();
        
        $this->totalHoursToday = $this->todayRecord ? $this->todayRecord->total_hours : 0;
        $this->totalHoursThisMonth = $this->user->getTotalHoursForMonth(now()->month, now()->year);
        
        $this->recentAnnouncements = Announcement::getActiveAnnouncements()->take(3);
        $this->myLeaveRequests = $this->user->leaveRequests()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function clockIn()
    {
        if ($this->isClockedIn) {
            return;
        }

        try {
            // Check if there's already a time record for today
            $existingRecord = $this->user->todayTimeRecord();
            
            if ($existingRecord) {
                // If there's an existing record, update it to start a new session
                $existingRecord->update([
                    'clock_in' => now(),
                    'clock_out' => null,
                    'total_hours' => 0,
                    'overtime_hours' => 0,
                    'status' => 'active',
                    'notes' => null, // Clear any previous notes
                ]);
                $this->todayRecord = $existingRecord;
            } else {
                // Create a new time record
                $this->todayRecord = TimeRecord::getOrCreateForUserOnDate(
                    $this->user->id,
                    today(),
                    [
                        'clock_in' => now(),
                        'status' => 'active',
                    ]
                );
            }

            $this->isClockedIn = true;
            $this->loadDashboardData();
            
            // Add a flash message
            session()->flash('message', 'Successfully clocked in at ' . now()->format('g:i A'));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error clocking in: ' . $e->getMessage());
        }
    }

    public function clockOut()
    {
        if (!$this->isClockedIn || !$this->todayRecord) {
            return;
        }

        // End any active break first
        if ($this->isOnBreak) {
            $this->endBreak();
        }

        $this->todayRecord->update([
            'clock_out' => now(),
            'total_hours' => $this->todayRecord->calculateTotalHours(),
            'overtime_hours' => $this->todayRecord->calculateOvertimeHours(),
            'status' => 'completed',
            'notes' => $this->clockOutNote, // Save the note
        ]);

        $this->isClockedIn = false;
        $this->clockOutNote = ''; // Clear the note
        $this->loadDashboardData();
        
        // Add a flash message
        session()->flash('message', 'Successfully clocked out at ' . now()->format('g:i A'));
    }

    public function startBreak()
    {
        if (!$this->isClockedIn || $this->isOnBreak || !$this->todayRecord) {
            return;
        }

        $this->todayRecord->startBreak();
        $this->isOnBreak = true;
        $this->loadDashboardData();
        
        // Add a flash message
        session()->flash('message', 'Break started at ' . now()->format('g:i A'));
    }

    public function endBreak()
    {
        if (!$this->isOnBreak || !$this->todayRecord) {
            return;
        }

        $this->todayRecord->endBreak();
        $this->isOnBreak = false;
        $this->loadDashboardData();
        
        // Add a flash message
        session()->flash('message', 'Break ended at ' . now()->format('g:i A'));
    }

    public function getCurrentTime()
    {
        $this->currentTime = now()->format('g:i:s A');
    }

    public function render()
    {
        return view('livewire.employee.dashboard')
            ->layout('layouts.app');
    }
}
