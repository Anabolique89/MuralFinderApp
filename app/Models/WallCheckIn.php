<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WallCheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wall_id',
        'note',
        'images',
        'latitude',
        'longitude',
        'accuracy',
        'is_verified',
        'visit_purpose',
        'duration_minutes',
        'companions',
        'is_public',
    ];

    protected $casts = [
        'images' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'is_verified' => 'boolean',
        'duration_minutes' => 'integer',
        'companions' => 'array',
        'is_public' => 'boolean',
    ];

    const PURPOSE_VIEWING = 'viewing';
    const PURPOSE_PAINTING = 'painting';
    const PURPOSE_PHOTOGRAPHY = 'photography';
    const PURPOSE_MAINTENANCE = 'maintenance';
    const PURPOSE_OTHER = 'other';

    /**
     * Get the user who checked in
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the wall that was checked into
     */
    public function wall()
    {
        return $this->belongsTo(Wall::class);
    }

    /**
     * Check if the check-in is verified (location matches wall location)
     */
    public function verify(): void
    {
        // Calculate distance between check-in location and wall location
        $distance = $this->calculateDistanceToWall();
        
        // Consider verified if within 100 meters
        $this->update([
            'is_verified' => $distance <= 0.1, // 0.1 km = 100 meters
        ]);
    }

    /**
     * Calculate distance to wall in kilometers
     */
    public function calculateDistanceToWall(): float
    {
        if (!$this->wall || !$this->latitude || !$this->longitude) {
            return PHP_FLOAT_MAX;
        }

        $earthRadius = 6371; // Earth's radius in kilometers

        $latDiff = deg2rad($this->wall->latitude - $this->latitude);
        $lonDiff = deg2rad($this->wall->longitude - $this->longitude);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($this->wall->latitude)) *
             sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get the check-in's coordinates as array
     */
    public function getCoordinatesAttribute(): array
    {
        return [
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
        ];
    }
}
