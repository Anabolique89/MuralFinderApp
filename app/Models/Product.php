<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'description', 'slug', 'images', 'primary_image',
        'price', 'original_price', 'currency', 'is_negotiable', 'type', 'condition',
        'tags', 'materials', 'dimensions', 'weight', 'status', 'quantity',
        'is_unique', 'is_digital', 'location', 'latitude', 'longitude',
        'local_pickup', 'shipping_available', 'shipping_cost', 'shipping_regions',
        'artwork_id', 'category_id', 'views_count', 'favorites_count',
        'inquiries_count', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'is_negotiable' => 'boolean',
        'tags' => 'array',
        'materials' => 'array',
        'dimensions' => 'array',
        'weight' => 'decimal:2',
        'quantity' => 'integer',
        'is_unique' => 'boolean',
        'is_digital' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'local_pickup' => 'boolean',
        'shipping_available' => 'boolean',
        'shipping_cost' => 'decimal:2',
        'shipping_regions' => 'array',
        'views_count' => 'integer',
        'favorites_count' => 'integer',
        'inquiries_count' => 'integer',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_SOLD = 'sold';
    const STATUS_RESERVED = 'reserved';
    const STATUS_INACTIVE = 'inactive';

    const TYPE_ARTWORK = 'artwork';
    const TYPE_PRINT = 'print';
    const TYPE_MERCHANDISE = 'merchandise';
    const TYPE_COMMISSION = 'commission';
    const TYPE_SERVICE = 'service';
    const TYPE_OTHER = 'other';

    const CONDITION_NEW = 'new';
    const CONDITION_LIKE_NEW = 'like_new';
    const CONDITION_GOOD = 'good';
    const CONDITION_FAIR = 'fair';
    const CONDITION_POOR = 'poor';

    /**
     * Get the user who owns the product
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related artwork
     */
    public function artwork()
    {
        return $this->belongsTo(Artwork::class);
    }

    /**
     * Get the product category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope a query to only include active products
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include available products
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_DRAFT]);
    }

    /**
     * Scope a query to filter by type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by price range
     */
    public function scopeByPriceRange(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Check if product is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if product is sold
     */
    public function isSold(): bool
    {
        return $this->status === self::STATUS_SOLD;
    }

    /**
     * Check if product is available
     */
    public function isAvailable(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_DRAFT]);
    }

    /**
     * Mark product as sold
     */
    public function markAsSold(): void
    {
        $this->update(['status' => self::STATUS_SOLD]);
    }

    /**
     * Mark product as reserved
     */
    public function markAsReserved(): void
    {
        $this->update(['status' => self::STATUS_RESERVED]);
    }

    /**
     * Activate the product
     */
    public function activate(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the product's primary image
     */
    public function getPrimaryImageAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }
        
        if ($this->images && is_array($this->images) && count($this->images) > 0) {
            return $this->images[0];
        }
        
        return null;
    }

    /**
     * Get the discounted price
     */
    public function getDiscountedPriceAttribute(): ?float
    {
        if ($this->original_price && $this->original_price > $this->price) {
            return $this->price;
        }
        
        return null;
    }

    /**
     * Get the discount percentage
     */
    public function getDiscountPercentageAttribute(): ?int
    {
        if ($this->original_price && $this->original_price > $this->price) {
            return round((($this->original_price - $this->price) / $this->original_price) * 100);
        }
        
        return null;
    }

    /**
     * Check if product has discount
     */
    public function hasDiscount(): bool
    {
        return $this->original_price && $this->original_price > $this->price;
    }
}
