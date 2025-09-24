<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'device_id',
        'device_name',
        'app_version',
        'os_version',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the device token
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Register or update a device token
     */
    public static function registerToken(
        User $user,
        string $token,
        string $platform,
        array $deviceInfo = []
    ): self {
        return self::updateOrCreate(
            [
                'user_id' => $user->id,
                'token' => $token,
                'platform' => $platform,
            ],
            [
                'device_id' => $deviceInfo['device_id'] ?? null,
                'device_name' => $deviceInfo['device_name'] ?? null,
                'app_version' => $deviceInfo['app_version'] ?? null,
                'os_version' => $deviceInfo['os_version'] ?? null,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );
    }

    /**
     * Deactivate token
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Update last used timestamp
     */
    public function updateLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Scope for active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific platform
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Clean up old inactive tokens
     */
    public static function cleanupOldTokens(int $daysOld = 30): int
    {
        return self::where('is_active', false)
            ->where('updated_at', '<', now()->subDays($daysOld))
            ->delete();
    }
}
