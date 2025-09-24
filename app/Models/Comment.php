<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'commentable_id',
        'commentable_type',
        'parent_id',
        'content',
        'mentions',
        'is_edited',
        'edited_at',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'likes_count',
        'replies_count',
    ];

    protected $casts = [
        'mentions' => 'array',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'likes_count' => 'integer',
        'replies_count' => 'integer',
    ];

    const STATUS_PUBLISHED = 'published';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_REJECTED = 'rejected';
    const STATUS_HIDDEN = 'hidden';

    /**
     * Get the user who made the comment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the commentable model (artwork, post, wall)
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * Get the parent comment (for nested comments)
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the child comments (replies)
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Get the user who reviewed the comment
     */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the comment's likes
     */
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /**
     * Scope a query to only include published comments
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope a query to only include top-level comments
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include replies
     */
    public function scopeReplies(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Check if comment is published
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Check if comment is a reply
     */
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Check if comment is top-level
     */
    public function isTopLevel(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Mark comment as edited
     */
    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }

    /**
     * Hide the comment
     */
    public function hide(): void
    {
        $this->update(['status' => self::STATUS_HIDDEN]);
    }

    /**
     * Publish the comment
     */
    public function publish(): void
    {
        $this->update(['status' => self::STATUS_PUBLISHED]);
    }
}
