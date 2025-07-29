<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get type icon
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'info' => 'info-circle',
            'success' => 'check-circle',
            'warning' => 'exclamation-triangle',
            'error' => 'x-circle',
            default => 'bell',
        };
    }

    /**
     * Get type color
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'info' => 'text-blue-600',
            'success' => 'text-green-600',
            'warning' => 'text-yellow-600',
            'error' => 'text-red-600',
            default => 'text-gray-600',
        };
    }

    /**
     * Get formatted created date
     */
    public function getFormattedCreatedDateAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->is_read;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Get unread notifications for user
     */
    public static function getUnreadForUser(User $user)
    {
        return self::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get read notifications for user
     */
    public static function getReadForUser(User $user)
    {
        return self::where('user_id', $user->id)
            ->where('is_read', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create notification for user
     */
    public static function createForUser(User $user, string $type, string $title, string $message, array $data = null): self
    {
        return self::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'is_read' => false,
        ]);
    }
}
