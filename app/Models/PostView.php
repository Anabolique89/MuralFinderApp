<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostView extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'referrer',
        'view_duration',
        'is_unique_view',
        'latitude',
        'longitude',
        'viewed_at',
    ];

    protected $casts = [
        'view_duration' => 'integer',
        'is_unique_view' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'viewed_at' => 'datetime',
    ];

    public $timestamps = false;

    protected $dates = ['viewed_at'];

    /**
     * Get the post that was viewed
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who viewed the post (nullable for anonymous views)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a post view
     */
    public static function recordView(Post $post, User $user = null, array $context = []): self
    {
        // Check if this is a unique view
        $isUniqueView = true;
        if ($user) {
            $isUniqueView = !self::where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->where('is_unique_view', true)
                ->exists();
        } else {
            // For anonymous users, check by IP address
            $ipAddress = $context['ip_address'] ?? request()->ip();
            $isUniqueView = !self::where('post_id', $post->id)
                ->where('ip_address', $ipAddress)
                ->where('is_unique_view', true)
                ->exists();
        }

        return self::create([
            'post_id' => $post->id,
            'user_id' => $user?->id,
            'ip_address' => $context['ip_address'] ?? request()->ip(),
            'user_agent' => $context['user_agent'] ?? request()->userAgent(),
            'device_type' => $context['device_type'] ?? null,
            'referrer' => $context['referrer'] ?? request()->header('referer'),
            'view_duration' => $context['view_duration'] ?? null,
            'is_unique_view' => $isUniqueView,
            'latitude' => $context['latitude'] ?? null,
            'longitude' => $context['longitude'] ?? null,
            'viewed_at' => now(),
        ]);
    }

    /**
     * Get view statistics for a post
     */
    public static function getPostStats(Post $post): array
    {
        $views = self::where('post_id', $post->id);

        return [
            'total_views' => $views->count(),
            'unique_views' => $views->where('is_unique_view', true)->count(),
            'registered_views' => $views->whereNotNull('user_id')->count(),
            'anonymous_views' => $views->whereNull('user_id')->count(),
            'average_duration' => $views->whereNotNull('view_duration')->avg('view_duration'),
            'views_today' => $views->whereDate('viewed_at', today())->count(),
            'views_this_week' => $views->where('viewed_at', '>=', now()->startOfWeek())->count(),
            'views_this_month' => $views->where('viewed_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * Get top referrers for a post
     */
    public static function getTopReferrers(Post $post, int $limit = 10): array
    {
        return self::where('post_id', $post->id)
            ->whereNotNull('referrer')
            ->selectRaw('referrer, COUNT(*) as count')
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('count', 'referrer')
            ->toArray();
    }

    /**
     * Get view trends for a post
     */
    public static function getViewTrends(Post $post, int $days = 30): array
    {
        return self::where('post_id', $post->id)
            ->where('viewed_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('views', 'date')
            ->toArray();
    }
}
