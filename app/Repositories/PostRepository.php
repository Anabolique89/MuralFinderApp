<?php

namespace App\Repositories;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PostRepository extends BaseRepository
{
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    /**
     * Get published posts
     */
    public function getPublished(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->published()
            ->with(['user.profile', 'category'])
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    /**
     * Get featured posts
     */
    public function getFeatured(int $limit = 5): Collection
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
     * Get pinned posts
     */
    public function getPinned(): Collection
    {
        return $this->model
            ->pinned()
            ->published()
            ->with(['user.profile', 'category'])
            ->orderByDesc('published_at')
            ->get();
    }

    /**
     * Get posts by user
     */
    public function getByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $user->id)
            ->with(['category'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get posts by category
     */
    public function getByCategory(Category $category, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('category_id', $category->id)
            ->published()
            ->with(['user.profile'])
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    /**
     * Get posts by type
     */
    public function getByType(string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->byType($type)
            ->published()
            ->with(['user.profile', 'category'])
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    /**
     * Search posts
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $queryBuilder = $this->model
            ->published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('content', 'LIKE', "%{$query}%")
                  ->orWhere('excerpt', 'LIKE', "%{$query}%")
                  ->orWhere('tags', 'LIKE', "%{$query}%");
            });

        // Apply filters
        if (isset($filters['category_id'])) {
            $queryBuilder->where('category_id', $filters['category_id']);
        }

        if (isset($filters['type'])) {
            $queryBuilder->where('type', $filters['type']);
        }

        if (isset($filters['user_id'])) {
            $queryBuilder->where('user_id', $filters['user_id']);
        }

        return $queryBuilder
            ->with(['user.profile', 'category'])
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    /**
     * Get most liked posts
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
     * Get most commented posts
     */
    public function getMostCommented(int $limit = 10): Collection
    {
        return $this->model
            ->published()
            ->orderByDesc('comments_count')
            ->with(['user.profile', 'category'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent posts
     */
    public function getRecent(int $limit = 10): Collection
    {
        return $this->model
            ->published()
            ->orderByDesc('published_at')
            ->with(['user.profile', 'category'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending posts (most engagement in last week)
     */
    public function getTrending(int $limit = 10): Collection
    {
        return $this->model
            ->published()
            ->where('published_at', '>=', now()->subWeek())
            ->orderByRaw('(likes_count + comments_count + views_count) DESC')
            ->with(['user.profile', 'category'])
            ->limit($limit)
            ->get();
    }
}
