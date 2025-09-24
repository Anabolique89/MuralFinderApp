<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Artwork extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'category_id',
        'wall_id',
        'primary_image_path',
        'images',
        'thumbnail_path',
        'tags',
        'colors',
        'style',
        'technique',
        'created_date',
        'is_commissioned',
        'commissioner',
        'latitude',
        'longitude',
        'location_text',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'likes_count',
        'comments_count',
        'views_count',
        'shares_count',
        'rating',
        'ratings_count',
        'slug',
        'is_featured',
        'featured_at',
    ];

    protected $casts = [
        'images' => 'array',
        'tags' => 'array',
        'colors' => 'array',
        'created_date' => 'date',
        'is_commissioned' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'reviewed_at' => 'datetime',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'views_count' => 'integer',
        'shares_count' => 'integer',
        'rating' => 'decimal:2',
        'ratings_count' => 'integer',
        'is_featured' => 'boolean',
        'featured_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ARCHIVED = 'archived';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function wall()
    {
        return $this->belongsTo(Wall::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function views()
    {
        return $this->hasMany(ArtworkView::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory(Builder $query, $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    public function publish(): void
    {
        $this->update(['status' => self::STATUS_PUBLISHED]);
    }

    public function feature(): void
    {
        $this->update([
            'is_featured' => true,
            'featured_at' => now(),
        ]);
    }

    public function unfeature(): void
    {
        $this->update([
            'is_featured' => false,
            'featured_at' => null,
        ]);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getPrimaryImageAttribute(): ?string
    {
        if ($this->primary_image_path) {
            return $this->primary_image_path;
        }

        if ($this->images && is_array($this->images) && count($this->images) > 0) {
            return $this->images[0];
        }

        return null;
    }
}
