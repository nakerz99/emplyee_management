<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BreakSession extends Model
{
    use HasFactory;

    protected $table = 'break_sessions';

    protected $fillable = [
        'time_record_id',
        'break_start',
        'break_end',
        'total_break_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'break_start' => 'datetime',
        'break_end' => 'datetime',
        'total_break_time' => 'decimal:2',
    ];

    /**
     * Get the time record that owns the break session.
     */
    public function timeRecord()
    {
        return $this->belongsTo(TimeRecord::class);
    }

    /**
     * Get the user through time record.
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, TimeRecord::class, 'id', 'id', 'time_record_id', 'user_id');
    }

    /**
     * Get duration in minutes
     */
    public function getDurationInMinutes(): int
    {
        if (!$this->break_start || !$this->break_end) {
            return 0;
        }

        return $this->break_start->diffInMinutes($this->break_end);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->getDurationInMinutes();
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return sprintf('%d:%02d', $hours, $remainingMinutes);
    }

    /**
     * Check if break is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if break is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
