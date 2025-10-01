<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'content',
        'status',
        'admin_notes',
        'responded_at',
        'responded_by',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESPONDED = 'responded';
    const STATUS_CLOSED = 'closed';

    /**
     * Get the admin user who responded to this message
     */
    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * Scope a query to only include pending messages
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include responded messages
     */
    public function scopeResponded($query)
    {
        return $query->where('status', self::STATUS_RESPONDED);
    }

    /**
     * Mark message as responded
     */
    public function markAsResponded($adminId = null)
    {
        $this->update([
            'status' => self::STATUS_RESPONDED,
            'responded_at' => now(),
            'responded_by' => $adminId,
        ]);
    }

    /**
     * Check if message has been responded to
     */
    public function isResponded(): bool
    {
        return $this->status === self::STATUS_RESPONDED;
    }

    /**
     * Check if message is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
