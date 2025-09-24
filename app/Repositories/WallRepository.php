<?php

namespace App\Repositories;

use App\Models\Wall;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WallRepository extends BaseRepository
{
    public function __construct(Wall $model)
    {
        parent::__construct($model);
    }

    /**
     * Get verified walls
     */
    public function getVerified(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->verified()
            ->with(['addedBy.profile', 'verifiedBy'])
            ->orderByDesc('verified_at')
            ->paginate($perPage);
    }

    /**
     * Get pending walls
     */
    public function getPending(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->pending()
            ->with(['addedBy.profile'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get walls by user
     */
    public function getByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('added_by', $user->id)
            ->with(['verifiedBy'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Search walls
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $queryBuilder = $this->model
            ->verified()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('location_text', 'LIKE', "%{$query}%")
                  ->orWhere('address', 'LIKE', "%{$query}%")
                  ->orWhere('city', 'LIKE', "%{$query}%");
            });

        // Apply filters
        if (isset($filters['wall_type'])) {
            $queryBuilder->where('wall_type', $filters['wall_type']);
        }

        if (isset($filters['surface_type'])) {
            $queryBuilder->where('surface_type', $filters['surface_type']);
        }

        if (isset($filters['is_legal'])) {
            $queryBuilder->where('is_legal', $filters['is_legal']);
        }

        if (isset($filters['city'])) {
            $queryBuilder->where('city', $filters['city']);
        }

        if (isset($filters['country'])) {
            $queryBuilder->where('country', $filters['country']);
        }

        return $queryBuilder
            ->with(['addedBy.profile'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get nearby walls
     */
    public function getNearby(float $latitude, float $longitude, float $radius = 10, int $limit = 20): Collection
    {
        return $this->model
            ->verified()
            ->nearby($latitude, $longitude, $radius)
            ->with(['addedBy.profile'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get walls by location
     */
    public function getByLocation(string $city, string $country = null): Collection
    {
        $query = $this->model->verified()->where('city', $city);
        
        if ($country) {
            $query->where('country', $country);
        }

        return $query
            ->with(['addedBy.profile'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get most popular walls (by artwork count)
     */
    public function getMostPopular(int $limit = 10): Collection
    {
        return $this->model
            ->verified()
            ->orderByDesc('artworks_count')
            ->with(['addedBy.profile'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get walls with most check-ins
     */
    public function getMostVisited(int $limit = 10): Collection
    {
        return $this->model
            ->verified()
            ->orderByDesc('check_ins_count')
            ->with(['addedBy.profile'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get legal walls
     */
    public function getLegalWalls(): Collection
    {
        return $this->model
            ->verified()
            ->where('is_legal', true)
            ->with(['addedBy.profile'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get walls by type
     */
    public function getByType(string $type): Collection
    {
        return $this->model
            ->verified()
            ->where('wall_type', $type)
            ->with(['addedBy.profile'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get walls needing verification
     */
    public function getNeedingVerification(int $limit = 50): Collection
    {
        return $this->model
            ->pending()
            ->orderBy('created_at')
            ->with(['addedBy.profile'])
            ->limit($limit)
            ->get();
    }
}
