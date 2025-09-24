<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, MustVerifyEmail, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'status',
        'provider',
        'provider_id',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * User roles
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_ARTIST = 'artist';
    const ROLE_ARTLOVER = 'artlover';
    const ROLE_MODERATOR = 'moderator';

    /**
     * User statuses
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_BANNED = 'banned';

    // Relationships

    /**
     * Get the user's profile
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get users that follow this user
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'following_id', 'follower_id')
                    ->withTimestamps()
                    ->withPivot('followed_at', 'is_mutual');
    }

    /**
     * Get users that this user follows
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'following_id')
                    ->withTimestamps()
                    ->withPivot('followed_at', 'is_mutual');
    }

    /**
     * Get the user's artworks
     */
    public function artworks()
    {
        return $this->hasMany(Artwork::class);
    }

    /**
     * Get the user's posts
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function newWalls(){
        return $this->hasMany(NewWall::class); // 1 User -> HasMany New Walls
    }

    /**
     * Get the user's walls
     */
    public function walls()
    {
        return $this->hasMany(Wall::class, 'added_by');
    }

    /**
     * Get walls verified by this user
     */
    public function verifiedWalls()
    {
        return $this->hasMany(Wall::class, 'verified_by');
    }

    /**
     * Get the user's products
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the user's likes
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get the user's comments
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the user's notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the user's notification preferences
     */
    public function notificationPreferences()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * Get the user's device tokens for push notifications
     */
    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    /**
     * Get the user's activity logs
     */
    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class);
    }

    /**
     * Get the user's wall check-ins
     */
    public function wallCheckIns()
    {
        return $this->hasMany(WallCheckIn::class);
    }

    /**
     * Get messages sent by this user
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get messages received by this user
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    /**
     * Get reports made by this user
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    /**
     * Get reports against this user
     */
    public function reportedReports()
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    // Helper Methods

    /**
     * Check if user is following another user
     */
    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    /**
     * Follow a user
     */
    public function follow(User $user): void
    {
        if (!$this->isFollowing($user)) {
            $this->following()->attach($user->id, [
                'followed_at' => now(),
                'is_mutual' => $user->isFollowing($this),
            ]);

            // Update mutual status for the other user if they're following back
            if ($user->isFollowing($this)) {
                $user->following()->updateExistingPivot($this->id, ['is_mutual' => true]);
            }
        }
    }

    /**
     * Unfollow a user
     */
    public function unfollow(User $user): void
    {
        $this->following()->detach($user->id);

        // Update mutual status for the other user
        if ($user->isFollowing($this)) {
            $user->following()->updateExistingPivot($this->id, ['is_mutual' => false]);
        }
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     * Check if user is artist
     */
    public function isArtist(): bool
    {
        return $this->hasRole(self::ROLE_ARTIST);
    }

    /**
     * Check if user is moderator
     */
    public function isModerator(): bool
    {
        return $this->hasRole(self::ROLE_MODERATOR);
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get user's full name from profile
     */
    public function getFullNameAttribute(): ?string
    {
        if ($this->profile) {
            return trim($this->profile->first_name . ' ' . $this->profile->last_name);
        }
        return null;
    }

    /**
     * Get user's display name (full name or username)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?: $this->username;
    }
}
