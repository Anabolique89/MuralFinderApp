<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reportable_id',
        'reportable_type',
        'type',
        'reason',
        'evidence',
        'status',
        'reviewed_by',
        'reviewed_at',
        'resolution_notes',
        'action_taken',
        'priority',
        'is_anonymous',
        'ip_address',
        'metadata',
    ];

    protected $casts = [
        'evidence' => 'array',
        'reviewed_at' => 'datetime',
        'is_anonymous' => 'boolean',
        'metadata' => 'array',
    ];

    const TYPE_INAPPROPRIATE_CONTENT = 'inappropriate_content';
    const TYPE_SPAM = 'spam';
    const TYPE_HARASSMENT = 'harassment';
    const TYPE_COPYRIGHT_VIOLATION = 'copyright_violation';
    const TYPE_FAKE_ARTWORK = 'fake_artwork';
    const TYPE_OFFENSIVE_LANGUAGE = 'offensive_language';
    const TYPE_VIOLENCE = 'violence';
    const TYPE_HATE_SPEECH = 'hate_speech';
    const TYPE_MISINFORMATION = 'misinformation';
    const TYPE_OTHER = 'other';

    const STATUS_PENDING = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_DISMISSED = 'dismissed';
    const STATUS_ESCALATED = 'escalated';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const ACTION_NONE = 'none';
    const ACTION_WARNING_SENT = 'warning_sent';
    const ACTION_CONTENT_REMOVED = 'content_removed';
    const ACTION_USER_SUSPENDED = 'user_suspended';
    const ACTION_USER_BANNED = 'user_banned';
    const ACTION_CONTENT_EDITED = 'content_edited';
    const ACTION_OTHER = 'other';

    /**
     * Get the user who made the report
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the reported user
     */
    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    /**
     * Get the reported content
     */
    public function reportable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who reviewed the report
     */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope a query to only include pending reports
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include under review reports
     */
    public function scopeUnderReview(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_UNDER_REVIEW);
    }

    /**
     * Scope a query to filter by priority
     */
    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to filter by type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Check if report is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if report is under review
     */
    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    /**
     * Check if report is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    /**
     * Mark report as under review
     */
    public function markAsUnderReview(User $reviewer): void
    {
        $this->update([
            'status' => self::STATUS_UNDER_REVIEW,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Resolve the report
     */
    public function resolve(User $reviewer, string $action, string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'action_taken' => $action,
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Dismiss the report
     */
    public function dismiss(User $reviewer, string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_DISMISSED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Escalate the report
     */
    public function escalate(User $reviewer): void
    {
        $this->update([
            'status' => self::STATUS_ESCALATED,
            'priority' => self::PRIORITY_HIGH,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Get the report type label
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_INAPPROPRIATE_CONTENT => 'Inappropriate Content',
            self::TYPE_SPAM => 'Spam',
            self::TYPE_HARASSMENT => 'Harassment',
            self::TYPE_COPYRIGHT_VIOLATION => 'Copyright Violation',
            self::TYPE_FAKE_ARTWORK => 'Fake Artwork',
            self::TYPE_OFFENSIVE_LANGUAGE => 'Offensive Language',
            self::TYPE_VIOLENCE => 'Violence',
            self::TYPE_HATE_SPEECH => 'Hate Speech',
            self::TYPE_MISINFORMATION => 'Misinformation',
            default => 'Other',
        };
    }
}
