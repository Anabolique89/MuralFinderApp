<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'likeable_id',
        'likeable_type',
        'reaction_type',
    ];

    const REACTION_LIKE = 'like';
    const REACTION_LOVE = 'love';
    const REACTION_WOW = 'wow';
    const REACTION_LAUGH = 'laugh';
    const REACTION_SAD = 'sad';
    const REACTION_ANGRY = 'angry';

    /**
     * Get the user who made the like
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the likeable model (artwork, post, wall, comment)
     */
    public function likeable()
    {
        return $this->morphTo();
    }

    /**
     * Check if this is a specific reaction type
     */
    public function isReaction(string $type): bool
    {
        return $this->reaction_type === $type;
    }

    /**
     * Check if this is a like reaction
     */
    public function isLike(): bool
    {
        return $this->isReaction(self::REACTION_LIKE);
    }

    /**
     * Check if this is a love reaction
     */
    public function isLove(): bool
    {
        return $this->isReaction(self::REACTION_LOVE);
    }
}
