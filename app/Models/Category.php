<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color_code',
        'icon',
        'is_active',
        'sort_order',
        'artworks_count',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'artworks_count' => 'integer',
    ];

    /**
     * Get the artworks for the category
     */
    public function artworks()
    {
        return $this->hasMany(Artwork::class);
    }

    /**
     * Get the posts for the category
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the products for the category
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope a query to only include active categories
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order categories by sort order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
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
     * Get the category's color with fallback
     */
    public function getColorAttribute(): string
    {
        return $this->color_code ?: '#6B7280';
    }
}
