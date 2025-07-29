<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'priority',
        'author_id',
        'is_active',
        'scheduled_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the author of the announcement.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get priority name
     */
    public function getPriorityNameAttribute(): string
    {
        return ucfirst($this->priority);
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match($this->priority) {
            'low' => 'bg-gray-100 text-gray-800',
            'normal' => 'bg-blue-100 text-blue-800',
            'high' => 'bg-orange-100 text-orange-800',
            'urgent' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get formatted scheduled date
     */
    public function getFormattedScheduledDateAttribute(): string
    {
        return $this->scheduled_at ? $this->scheduled_at->format('M j, Y g:i A') : 'Immediate';
    }

    /**
     * Get formatted expiry date
     */
    public function getFormattedExpiryDateAttribute(): string
    {
        return $this->expires_at ? $this->expires_at->format('M j, Y g:i A') : 'No expiry';
    }

    /**
     * Check if announcement is active
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        // Check if scheduled for future
        if ($this->scheduled_at && $this->scheduled_at->isFuture()) {
            return false;
        }

        // Check if expired
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Get active announcements
     */
    public static function getActiveAnnouncements()
    {
        return self::where('is_active', true)
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
            ->get();
    }

    /**
     * Get urgent announcements
     */
    public static function getUrgentAnnouncements()
    {
        return self::getActiveAnnouncements()->where('priority', 'urgent');
    }

    /**
     * Scope for active announcements
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }
}
