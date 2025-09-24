<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?User
    {
        return $this->model->where('username', $username)->first();
    }

    /**
     * Search users by username or email
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('username', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhereHas('profile', function ($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%");
            })
            ->with('profile')
            ->paginate($perPage);
    }

    /**
     * Get active users
     */
    public function getActiveUsers(): Collection
    {
        return $this->model->where('status', User::STATUS_ACTIVE)->get();
    }

    /**
     * Get users by role
     */
    public function getUsersByRole(string $role): Collection
    {
        return $this->model->where('role', $role)->get();
    }

    /**
     * Get users with most followers
     */
    public function getMostFollowed(int $limit = 10): Collection
    {
        return $this->model
            ->withCount('followers')
            ->orderByDesc('followers_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recently joined users
     */
    public function getRecentlyJoined(int $limit = 10): Collection
    {
        return $this->model
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get users by location
     */
    public function getUsersByLocation(string $city, string $country = null): Collection
    {
        return $this->model
            ->whereHas('profile', function ($q) use ($city, $country) {
                $q->where('city', $city);
                if ($country) {
                    $q->where('country', $country);
                }
            })
            ->with('profile')
            ->get();
    }

    /**
     * Update last login
     */
    public function updateLastLogin(User $user): bool
    {
        return $user->update(['last_login_at' => now()]);
    }
}
