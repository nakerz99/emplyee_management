<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class TimeRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'total_hours',
        'break_hours',
        'overtime_hours',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'total_hours' => 'decimal:2',
        'break_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    /**
     * Get validation rules for creating a time record
     */
    public static function getValidationRules($ignoreId = null)
    {
        return [
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'clock_in' => 'nullable|date',
            'clock_out' => 'nullable|date|after_or_equal:clock_in',
            'total_hours' => 'nullable|numeric|min:0',
            'break_hours' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,completed,absent',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Get unique validation rule for user_id and date combination
     */
    public static function getUniqueValidationRule($ignoreId = null)
    {
        $rule = Rule::unique('time_records')->where(function ($query) {
            return $query->where('user_id', request('user_id'))
                        ->where('date', request('date'));
        });

        if ($ignoreId) {
            $rule->ignore($ignoreId);
        }

        return $rule;
    }

    /**
     * Check if a time record exists for a user on a specific date
     */
    public static function existsForUserOnDate($userId, $date)
    {
        return static::where('user_id', $userId)
                    ->where('date', $date)
                    ->exists();
    }

    /**
     * Get or create a time record for a user on a specific date
     */
    public static function getOrCreateForUserOnDate($userId, $date, $attributes = [])
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'date' => $date,
            ],
            array_merge([
                'status' => 'active',
            ], $attributes)
        );
    }

    /**
     * Get the user that owns the time record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the break sessions for the time record.
     */
    public function breakSessions()
    {
        return $this->hasMany(BreakSession::class);
    }

    /**
     * Calculate total hours worked
     */
    public function calculateTotalHours(): float
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $totalMinutes = $this->clock_in->diffInMinutes($this->clock_out);
        $breakMinutes = $this->getTotalBreakTime() * 60;
        
        return round(($totalMinutes - $breakMinutes) / 60, 2);
    }

    /**
     * Calculate overtime hours (over 8 hours)
     */
    public function calculateOvertimeHours(): float
    {
        $regularHours = 8.0;
        $totalHours = $this->calculateTotalHours();
        
        return max(0, $totalHours - $regularHours);
    }

    /**
     * Get current active break
     */
    public function getCurrentBreak()
    {
        return $this->breakSessions()->where('status', 'active')->first();
    }

    /**
     * Check if currently on break
     */
    public function isOnBreak(): bool
    {
        return $this->getCurrentBreak() !== null;
    }

    /**
     * Start a break
     */
    public function startBreak(): BreakSession
    {
        return $this->breakSessions()->create([
            'break_start' => now(),
            'status' => 'active',
        ]);
    }

    /**
     * End current break
     */
    public function endBreak(): bool
    {
        $currentBreak = $this->getCurrentBreak();
        
        if (!$currentBreak) {
            return false;
        }

        $currentBreak->update([
            'break_end' => now(),
            'total_break_time' => $currentBreak->getDurationInMinutes() / 60,
            'status' => 'completed',
        ]);

        return true;
    }

    /**
     * Get total break time in hours
     */
    public function getTotalBreakTime(): float
    {
        return $this->breakSessions()
            ->where('status', 'completed')
            ->sum('total_break_time');
    }

    /**
     * Get formatted clock in time
     */
    public function getFormattedClockInAttribute(): string
    {
        return $this->clock_in ? $this->clock_in->format('g:i A') : 'Not clocked in';
    }

    /**
     * Get formatted clock out time
     */
    public function getFormattedClockOutAttribute(): string
    {
        return $this->clock_out ? $this->clock_out->format('g:i A') : 'Not clocked out';
    }

    /**
     * Get duration as string
     */
    public function getDurationAttribute(): string
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 'N/A';
        }

        $hours = floor($this->total_hours);
        $minutes = round(($this->total_hours - $hours) * 60);
        
        return sprintf('%d:%02d', $hours, $minutes);
    }
}
