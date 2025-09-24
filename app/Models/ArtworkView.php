<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtworkView extends Model
{
    use HasFactory;

    protected $fillable = [
        'artwork_id',
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'referrer',
        'view_duration',
        'is_unique_view',
        'latitude',
        'longitude',
        'viewed_at',
    ];

    protected $casts = [
        'view_duration' => 'integer',
        'is_unique_view' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'viewed_at' => 'datetime',
    ];

    public $timestamps = false;

    protected $dates = ['viewed_at'];

    /**
     * Get the artwork that was viewed
     */
    public function artwork()
    {
        return $this->belongsTo(Artwork::class);
    }

    /**
     * Get the user who viewed the artwork (nullable for anonymous views)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an artwork view
     */
    public static function recordView(
        Artwork $artwork,
        User $user = null,
        array $context = []
    ): self {
        $ipAddress = $context['ip_address'] ?? request()->ip();
        
        // Check if this is a unique view
        $isUniqueView = !self::where('artwork_id', $artwork->id)
            ->where(function ($query) use ($user, $ipAddress) {
                if ($user) {
                    $query->where('user_id', $user->id);
                } else {
                    $query->where('ip_address', $ipAddress);
                }
            })
            ->exists();

        return self::create([
            'artwork_id' => $artwork->id,
            'user_id' => $user?->id,
            'ip_address' => $ipAddress,
            'user_agent' => $context['user_agent'] ?? request()->userAgent(),
            'device_type' => $context['device_type'] ?? null,
            'referrer' => $context['referrer'] ?? request()->header('referer'),
            'view_duration' => $context['view_duration'] ?? null,
            'is_unique_view' => $isUniqueView,
            'latitude' => $context['latitude'] ?? null,
            'longitude' => $context['longitude'] ?? null,
            'viewed_at' => now(),
        ]);
    }
}
