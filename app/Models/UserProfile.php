<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $table = 'user_profiles';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'bio',
        'date_of_birth',
        'profession',
        'country',
        'city',
        'location',
        'latitude',
        'longitude',
        'profile_image_url',
        'cover_image_url',
        'website',
        'instagram',
        'twitter',
        'facebook',
        'linkedin',
        'tiktok',
        'is_profile_public',
        'show_location',
        'show_email',
        'followers_count',
        'following_count',
        'artworks_count',
        'posts_count',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_profile_public' => 'boolean',
        'show_location' => 'boolean',
        'show_email' => 'boolean',
        'followers_count' => 'integer',
        'following_count' => 'integer',
        'artworks_count' => 'integer',
        'posts_count' => 'integer',
    ];

    /**
     * Get the user that owns the profile
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute(): ?string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the user's age from date of birth
     */
    public function getAgeAttribute(): ?int
    {
        if ($this->date_of_birth) {
            return $this->date_of_birth->age;
        }
        return null;
    }

    /**
     * Get social media links as array
     */
    public function getSocialLinksAttribute(): array
    {
        return array_filter([
            'instagram' => $this->instagram,
            'twitter' => $this->twitter,
            'facebook' => $this->facebook,
            'linkedin' => $this->linkedin,
            'tiktok' => $this->tiktok,
            'website' => $this->website,
        ]);
    }

    /**
     * Check if profile has location data
     */
    public function hasLocation(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /**
     * Increment followers count
     */
    public function incrementFollowersCount(): void
    {
        $this->increment('followers_count');
    }

    /**
     * Decrement followers count
     */
    public function decrementFollowersCount(): void
    {
        $this->decrement('followers_count');
    }

    /**
     * Increment following count
     */
    public function incrementFollowingCount(): void
    {
        $this->increment('following_count');
    }

    /**
     * Decrement following count
     */
    public function decrementFollowingCount(): void
    {
        $this->decrement('following_count');
    }

    /**
     * Increment artworks count
     */
    public function incrementArtworksCount(): void
    {
        $this->increment('artworks_count');
    }

    /**
     * Decrement artworks count
     */
    public function decrementArtworksCount(): void
    {
        $this->decrement('artworks_count');
    }

    /**
     * Increment posts count
     */
    public function incrementPostsCount(): void
    {
        $this->increment('posts_count');
    }

    /**
     * Decrement posts count
     */
    public function decrementPostsCount(): void
    {
        $this->decrement('posts_count');
    }
}
