<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model
{
    use HasFactory;

    protected $table = 'search_history';

    protected $fillable = [
        'user_id',
        'query',
        'search_type',
        'filters',
        'results_count',
        'had_results',
        'ip_address',
        'latitude',
        'longitude',
        'device_type',
        'click_position',
        'clicked_result_id',
        'clicked_result_type',
        'searched_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'results_count' => 'integer',
        'had_results' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'click_position' => 'integer',
        'searched_at' => 'datetime',
    ];

    public $timestamps = false;

    protected $dates = ['searched_at'];

    const TYPE_ARTWORKS = 'artworks';
    const TYPE_POSTS = 'posts';
    const TYPE_WALLS = 'walls';
    const TYPE_USERS = 'users';
    const TYPE_GLOBAL = 'global';

    /**
     * Get the user who performed the search (nullable for anonymous searches)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the clicked result model
     */
    public function clickedResult()
    {
        return $this->morphTo('clicked_result', 'clicked_result_type', 'clicked_result_id');
    }

    /**
     * Record a search
     */
    public static function recordSearch(
        string $query,
        string $searchType,
        int $resultsCount,
        User $user = null,
        array $filters = [],
        array $context = []
    ): self {
        return self::create([
            'user_id' => $user?->id,
            'query' => $query,
            'search_type' => $searchType,
            'filters' => $filters,
            'results_count' => $resultsCount,
            'had_results' => $resultsCount > 0,
            'ip_address' => $context['ip_address'] ?? request()->ip(),
            'latitude' => $context['latitude'] ?? null,
            'longitude' => $context['longitude'] ?? null,
            'device_type' => $context['device_type'] ?? null,
            'searched_at' => now(),
        ]);
    }

    /**
     * Record a click on a search result
     */
    public function recordClick(Model $result, int $position): void
    {
        $this->update([
            'click_position' => $position,
            'clicked_result_id' => $result->id,
            'clicked_result_type' => get_class($result),
        ]);
    }

    /**
     * Get popular search queries
     */
    public static function getPopularQueries(int $limit = 10): array
    {
        return self::selectRaw('query, COUNT(*) as search_count')
            ->where('had_results', true)
            ->where('searched_at', '>=', now()->subDays(30))
            ->groupBy('query')
            ->orderByDesc('search_count')
            ->limit($limit)
            ->pluck('search_count', 'query')
            ->toArray();
    }

    /**
     * Get search suggestions for a user
     */
    public static function getSuggestionsForUser(User $user, int $limit = 5): array
    {
        return self::where('user_id', $user->id)
            ->where('had_results', true)
            ->orderByDesc('searched_at')
            ->limit($limit)
            ->pluck('query')
            ->unique()
            ->values()
            ->toArray();
    }
}
