<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Wall extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'location_text', 'address', 'city', 'country',
        'latitude', 'longitude', 'image_path', 'images', 'added_by', 'verified_by',
        'status', 'verified_at', 'rejection_reason', 'wall_type', 'surface_type',
        'height', 'width', 'is_legal', 'requires_permission', 'artworks_count',
        'likes_count', 'comments_count', 'check_ins_count', 'views_count',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'images' => 'array',
        'verified_at' => 'datetime',
        'height' => 'decimal:2',
        'width' => 'decimal:2',
        'is_legal' => 'boolean',
        'requires_permission' => 'boolean',
        'artworks_count' => 'integer',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'check_ins_count' => 'integer',
        'views_count' => 'integer',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_VERIFIED = 'verified';
    const STATUS_REJECTED = 'rejected';

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function artworks()
    {
        return $this->hasMany(Artwork::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function checkIns()
    {
        return $this->hasMany(WallCheckIn::class);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function verify(User $verifier): void
    {
        $this->update([
            'status' => self::STATUS_VERIFIED,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(User $verifier, string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
