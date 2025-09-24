<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'content', 'excerpt', 'featured_image', 'images',
        'tags', 'slug', 'type', 'category_id', 'status', 'rejection_reason',
        'reviewed_by', 'reviewed_at', 'published_at', 'allow_comments',
        'is_pinned', 'is_featured', 'featured_at', 'likes_count', 'comments_count',
        'views_count', 'shares_count', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'images' => 'array',
        'tags' => 'array',
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
        'allow_comments' => 'boolean',
        'is_pinned' => 'boolean',
        'is_featured' => 'boolean',
        'featured_at' => 'datetime',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'views_count' => 'integer',
        'shares_count' => 'integer',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ARCHIVED = 'archived';

    const TYPE_ARTICLE = 'article';
    const TYPE_DISCUSSION = 'discussion';
    const TYPE_QUESTION = 'question';
    const TYPE_SHOWCASE = 'showcase';
    const TYPE_EVENT = 'event';
    const TYPE_NEWS = 'news';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
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

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
                    ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && 
               $this->published_at && 
               $this->published_at->isPast();
    }

    public function publish(): void
    {
        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function feature(): void
    {
        $this->update([
            'is_featured' => true,
            'featured_at' => now(),
        ]);
    }

    public function pin(): void
    {
        $this->update(['is_pinned' => true]);
    }

    public function unpin(): void
    {
        $this->update(['is_pinned' => false]);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }
        
        return \Str::limit(strip_tags($this->content), 150);
    }
}
