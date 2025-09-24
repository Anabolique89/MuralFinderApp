<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    /**
     * Get active categories
     */
    public function getActive(): Collection
    {
        return $this->model->active()->ordered()->get();
    }

    /**
     * Get categories with artwork counts
     */
    public function getWithArtworkCounts(): Collection
    {
        return $this->model
            ->active()
            ->withCount('artworks')
            ->ordered()
            ->get();
    }

    /**
     * Get categories with post counts
     */
    public function getWithPostCounts(): Collection
    {
        return $this->model
            ->active()
            ->withCount('posts')
            ->ordered()
            ->get();
    }

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Get most popular categories (by artwork count)
     */
    public function getMostPopular(int $limit = 10): Collection
    {
        return $this->model
            ->active()
            ->orderByDesc('artworks_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Search categories
     */
    public function search(string $query): Collection
    {
        return $this->model
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->ordered()
            ->get();
    }
}
