<?php

namespace App\Repositories;

use App\Models\Artwork;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ArtworkRepository extends BaseRepository
{
    public function __construct(Artwork $model)
    {
        parent::__construct($model);
    }

    /**
     * Find artwork by ID with relationships
     */
    public function findById(int $id): ?Artwork
    {
        return $this->model
            ->with(['user.profile', 'category', 'wall'])
            ->find($id);
    }

    /**
     * Get published artworks
     */
    public function getPublished(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->published()
            ->with(['user.profile', 'category', 'wall'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get featured artworks
     */
    public function getFeatured(int $limit = 10): Collection
    {
        return $this->model
            ->featured()
            ->published()
            ->with(['user.profile', 'category'])
            ->orderByDesc('featured_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get artworks by user
     */
    public function getByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $user->id)
            ->with(['user.profile', 'category', 'wall'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get artworks by category
     */
    public function getByCategory(Category $category, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->byCategory($category->id)
            ->published()
            ->with(['user.profile', 'wall'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Search artworks
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $queryBuilder = $this->model
            ->published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('tags', 'LIKE', "%{$query}%");
            });

        // Apply filters
        if (isset($filters['category_id'])) {
            $queryBuilder->where('category_id', $filters['category_id']);
        }

        if (isset($filters['style'])) {
            $queryBuilder->where('style', $filters['style']);
        }

        if (isset($filters['technique'])) {
            $queryBuilder->where('technique', $filters['technique']);
        }

        if (isset($filters['location'])) {
            $queryBuilder->where('location_text', 'LIKE', "%{$filters['location']}%");
        }

        return $queryBuilder
            ->with(['user.profile', 'category', 'wall'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get nearby artworks
     */
    public function getNearby(float $latitude, float $longitude, float $radius = 10, int $limit = 20): Collection
    {
        return $this->model
            ->published()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw("*, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance", [$latitude, $longitude, $latitude])
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->with(['user.profile', 'category'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get most liked artworks
     */
    public function getMostLiked(int $limit = 10): Collection
    {
        return $this->model
            ->published()
            ->orderByDesc('likes_count')
            ->with(['user.profile', 'category'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get most viewed artworks
     */
    public function getMostViewed(int $limit = 10): Collection
    {
        return $this->model
            ->published()
            ->orderByDesc('views_count')
            ->with(['user.profile', 'category'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent artworks
     */
    public function getRecent(int $limit = 10): Collection
    {
        return $this->model
            ->published()
            ->orderByDesc('created_at')
            ->with(['user.profile', 'category'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get artworks by wall
     */
    public function getByWall(int $wallId): Collection
    {
        return $this->model
            ->where('wall_id', $wallId)
            ->published()
            ->with(['user.profile', 'category'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get random artworks
     */
    public function getRandom(int $limit = 5): Collection
    {
        return $this->model
            ->published()
            ->inRandomOrder()
            ->with(['user.profile', 'category'])
            ->limit($limit)
            ->get();
    }
}
