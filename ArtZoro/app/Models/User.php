<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, MustVerifyEmail;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function followers()
    {
        return $this->hasMany(Fellowship::class, 'following_id');
    }

    public function followings()
    {
        return $this->hasMany(Fellowship::class, 'follower_id');
    }

    public function follow(User $user)
    {
        return $this->followings()->create([
            'follower_id' => $this->id, // set the follower_id as the current user's id
            'following_id' => $user->id, // set the following_id as the user's id to be followed
        ]);
    }

    public function isFollowing(User $user)
    {
        return $this->followings()->where('following_id', $user->id)->exists();
    }


    public function unfollow(User $user)
    {
        return $this->followings()->where('follower_id', $this->id)->where('following_id', $user->id)->delete();
    }

}
