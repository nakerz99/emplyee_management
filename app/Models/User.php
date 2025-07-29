<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'position',
        'department',
        'hourly_rate',
        'timezone',
        'status',
        'birthday',
        'phone',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
            'hourly_rate' => 'decimal:2',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is employee
     */
    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    /**
     * Get user's time records
     */
    public function timeRecords()
    {
        return $this->hasMany(TimeRecord::class);
    }

    /**
     * Get user's payslips
     */
    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    /**
     * Get user's leave requests
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get user's notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get user's announcements (if admin)
     */
    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'author_id');
    }

    /**
     * Get approved leave requests (if admin)
     */
    public function approvedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    /**
     * Get today's time record
     */
    public function todayTimeRecord()
    {
        return $this->timeRecords()->where('date', today())->first();
    }

    /**
     * Get current month's time records
     */
    public function currentMonthTimeRecords()
    {
        return $this->timeRecords()
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month);
    }

    /**
     * Check if user is currently clocked in
     */
    public function isClockedIn(): bool
    {
        $todayRecord = $this->todayTimeRecord();
        return $todayRecord && $todayRecord->clock_in && !$todayRecord->clock_out;
    }

    /**
     * Check if user is on break
     */
    public function isOnBreak(): bool
    {
        $todayRecord = $this->todayTimeRecord();
        if (!$todayRecord) return false;
        
        return $todayRecord->breakSessions()->where('status', 'active')->exists();
    }

    /**
     * Get birthday celebrants for current month
     */
    public static function getBirthdayCelebrants()
    {
        return self::whereMonth('birthday', now()->month)
            ->where('status', 'active')
            ->orderByRaw('DAY(birthday)')
            ->get();
    }

    /**
     * Get total hours worked for a specific month
     */
    public function getTotalHoursForMonth(int $month, int $year): float
    {
        return $this->timeRecords()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('total_hours');
    }

    /**
     * Get total pay for a specific month
     */
    public function getTotalPayForMonth(int $month, int $year): float
    {
        $totalHours = $this->getTotalHoursForMonth($month, $year);
        return $totalHours * $this->hourly_rate;
    }

    /**
     * Get user's current timezone
     */
    public function getCurrentTimezone(): string
    {
        return $this->timezone ?? 'America/New_York';
    }

    /**
     * Convert time to user's timezone
     */
    public function convertToUserTimezone($datetime)
    {
        return Carbon::parse($datetime)->setTimezone($this->getCurrentTimezone());
    }
}
