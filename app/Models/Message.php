<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'content',
        'attachments',
        'related_to_id',
        'related_to_type',
        'is_read',
        'read_at',
        'is_deleted_by_sender',
        'is_deleted_by_recipient',
        'type',
        'metadata',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'is_deleted_by_sender' => 'boolean',
        'is_deleted_by_recipient' => 'boolean',
        'metadata' => 'array',
    ];

    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_FILE = 'file';
    const TYPE_SYSTEM = 'system';
    const TYPE_INQUIRY = 'inquiry';

    /**
     * Get the sender of the message
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the recipient of the message
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the related model (artwork, post, product, etc.)
     */
    public function relatedTo()
    {
        return $this->morphTo('related_to', 'related_to_type', 'related_to_id');
    }

    /**
     * Scope a query to only include unread messages
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read messages
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope a query for messages between two users
     */
    public function scopeBetweenUsers(Builder $query, User $user1, User $user2): Builder
    {
        return $query->where(function ($q) use ($user1, $user2) {
            $q->where('sender_id', $user1->id)->where('recipient_id', $user2->id);
        })->orWhere(function ($q) use ($user1, $user2) {
            $q->where('sender_id', $user2->id)->where('recipient_id', $user1->id);
        });
    }

    /**
     * Scope a query for messages visible to a user
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)->where('is_deleted_by_sender', false);
        })->orWhere(function ($q) use ($user) {
            $q->where('recipient_id', $user->id)->where('is_deleted_by_recipient', false);
        });
    }

    /**
     * Check if message is read
     */
    public function isRead(): bool
    {
        return $this->is_read;
    }

    /**
     * Check if message is unread
     */
    public function isUnread(): bool
    {
        return !$this->is_read;
    }

    /**
     * Mark message as read
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
     * Mark message as unread
     */
    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Delete message for sender
     */
    public function deleteForSender(): void
    {
        $this->update(['is_deleted_by_sender' => true]);
    }

    /**
     * Delete message for recipient
     */
    public function deleteForRecipient(): void
    {
        $this->update(['is_deleted_by_recipient' => true]);
    }

    /**
     * Check if message is visible to a user
     */
    public function isVisibleTo(User $user): bool
    {
        if ($user->id === $this->sender_id) {
            return !$this->is_deleted_by_sender;
        }
        
        if ($user->id === $this->recipient_id) {
            return !$this->is_deleted_by_recipient;
        }
        
        return false;
    }

    /**
     * Get conversation between two users
     */
    public static function getConversation(User $user1, User $user2, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return self::betweenUsers($user1, $user2)
            ->visibleTo($user1)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Send a message
     */
    public static function send(
        User $sender,
        User $recipient,
        string $content,
        string $type = self::TYPE_TEXT,
        array $attachments = [],
        Model $relatedTo = null,
        array $metadata = []
    ): self {
        return self::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'content' => $content,
            'type' => $type,
            'attachments' => $attachments,
            'related_to_id' => $relatedTo?->id,
            'related_to_type' => $relatedTo ? get_class($relatedTo) : null,
            'metadata' => $metadata,
        ]);
    }
}
