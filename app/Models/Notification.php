<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'title',
        'message',
        'notifiable_id',
        'notifiable_type',
        'data',
        'action_url',
        'icon',
        'image_url',
        'is_read',
        'read_at',
        'is_sent_email',
        'email_sent_at',
        'is_sent_push',
        'push_sent_at',
        'priority',
        'group_key',
        'is_grouped',
        'group_count',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'is_sent_email' => 'boolean',
        'email_sent_at' => 'datetime',
        'is_sent_push' => 'boolean',
        'push_sent_at' => 'datetime',
        'is_grouped' => 'boolean',
        'group_count' => 'integer',
    ];

    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const TYPE_ARTWORK_LIKED = 'artwork_liked';
    const TYPE_ARTWORK_COMMENTED = 'artwork_commented';
    const TYPE_POST_LIKED = 'post_liked';
    const TYPE_POST_COMMENTED = 'post_commented';
    const TYPE_WALL_LIKED = 'wall_liked';
    const TYPE_WALL_COMMENTED = 'wall_commented';
    const TYPE_USER_FOLLOWED = 'user_followed';
    const TYPE_WALL_ADDED_NEARBY = 'wall_added_nearby';
    const TYPE_MENTIONED = 'mentioned';

    /**
     * Get the user who will receive the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who triggered the notification
     */
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get the notifiable model (artwork, post, wall, etc.)
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include unread notifications
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read notifications
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope a query to filter by priority
     */
    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
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
     * Mark email as sent
     */
    public function markEmailAsSent(): void
    {
        $this->update([
            'is_sent_email' => true,
            'email_sent_at' => now(),
        ]);
    }

    /**
     * Mark push notification as sent
     */
    public function markPushAsSent(): void
    {
        $this->update([
            'is_sent_push' => true,
            'push_sent_at' => now(),
        ]);
    }

    /**
     * Create a notification
     */
    public static function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        Model $notifiable = null,
        User $actor = null,
        array $data = [],
        string $priority = self::PRIORITY_NORMAL
    ): self {
        return self::create([
            'user_id' => $user->id,
            'actor_id' => $actor?->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'notifiable_id' => $notifiable?->id,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'data' => $data,
            'priority' => $priority,
        ]);
    }

    /**
     * Get the notification's display data
     */
    public function getDisplayDataAttribute(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'icon' => $this->icon,
            'image_url' => $this->image_url,
            'action_url' => $this->action_url,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at,
            'actor' => $this->actor ? [
                'id' => $this->actor->id,
                'username' => $this->actor->username,
                'display_name' => $this->actor->display_name,
            ] : null,
        ];
    }
}
