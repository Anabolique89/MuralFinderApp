<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'subject_id',
        'subject_type',
        'metadata',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'metadata' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
    ];

    // Only created_at, no updated_at
    public const UPDATED_AT = null;

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject model (artwork, post, wall, user)
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Log a user action
     */
    public static function logAction(
        User $user,
        string $action,
        Model $subject = null,
        array $metadata = [],
        array $context = []
    ): self {
        return self::create([
            'user_id' => $user->id,
            'action' => $action,
            'subject_id' => $subject?->id,
            'subject_type' => $subject ? get_class($subject) : null,
            'metadata' => $metadata,
            'ip_address' => $context['ip_address'] ?? request()->ip(),
            'user_agent' => $context['user_agent'] ?? request()->userAgent(),
            'device_type' => $context['device_type'] ?? null,
            'browser' => $context['browser'] ?? null,
            'platform' => $context['platform'] ?? null,
            'latitude' => $context['latitude'] ?? null,
            'longitude' => $context['longitude'] ?? null,
        ]);
    }
}
