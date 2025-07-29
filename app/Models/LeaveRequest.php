<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'days_requested',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'admin_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the leave request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved the leave request.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get leave type name
     */
    public function getLeaveTypeNameAttribute(): string
    {
        return ucfirst($this->leave_type);
    }

    /**
     * Get status name
     */
    public function getStatusNameAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Get formatted date range
     */
    public function getFormattedDateRangeAttribute(): string
    {
        $start = $this->start_date->format('M j, Y');
        $end = $this->end_date->format('M j, Y');
        
        return $start === $end ? $start : "$start - $end";
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the leave request
     */
    public function approve(User $admin, string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Reject the leave request
     */
    public function reject(User $admin, string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'admin_notes' => $notes,
        ]);
    }
}
