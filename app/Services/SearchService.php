<?php

namespace App\Services;

use App\Models\User;
use App\Models\SearchHistory;
use App\Repositories\ArtworkRepository;
use App\Repositories\PostRepository;
use App\Repositories\WallRepository;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Model;

class SearchService
{
    protected ArtworkRepository $artworkRepository;
    protected PostRepository $postRepository;
    protected WallRepository $wallRepository;
    protected UserRepository $userRepository;

    public function __construct(
        ArtworkRepository $artworkRepository,
        PostRepository $postRepository,
        WallRepository $wallRepository,
        UserRepository $userRepository
    ) {
        $this->artworkRepository = $artworkRepository;
        $this->postRepository = $postRepository;
        $this->wallRepository = $wallRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Global search across all content types
     */
    public function globalSearch(
        string $query,
        array $filters = [],
        int $perPage = 15,
        User $user = null
    ): array {
        $results = [
            'artworks' => $this->searchArtworks($query, $filters, $perPage),
            'posts' => $this->searchPosts($query, $filters, $perPage),
            'walls' => $this->searchWalls($query, $filters, $perPage),
            'users' => $this->searchUsers($query, $perPage),
        ];

        // Record search
        $totalResults = $results['artworks']->total() + 
                       $results['posts']->total() + 
                       $results['walls']->total() + 
                       $results['users']->total();

        $this->recordSearch($query, 'global', $totalResults, $user, $filters);

        return $results;
    }

    /**
     * Search artworks
     */
    public function searchArtworks(
        string $query,
        array $filters = [],
        int $perPage = 15,
        User $user = null
    ) {
        $results = $this->artworkRepository->search($query, $filters, $perPage);
        
        if ($user) {
            $this->recordSearch($query, 'artworks', $results->total(), $user, $filters);
        }

        return $results;
    }

    /**
     * Search posts
     */
    public function searchPosts(
        string $query,
        array $filters = [],
        int $perPage = 15,
        User $user = null
    ) {
        $results = $this->postRepository->search($query, $filters, $perPage);
        
        if ($user) {
            $this->recordSearch($query, 'posts', $results->total(), $user, $filters);
        }

        return $results;
    }

    /**
     * Search walls
     */
    public function searchWalls(
        string $query,
        array $filters = [],
        int $perPage = 15,
        User $user = null
    ) {
        $results = $this->wallRepository->search($query, $filters, $perPage);
        
        if ($user) {
            $this->recordSearch($query, 'walls', $results->total(), $user, $filters);
        }

        return $results;
    }

    /**
     * Search users
     */
    public function searchUsers(
        string $query,
        int $perPage = 15,
        User $user = null
    ) {
        $results = $this->userRepository->search($query, $perPage);
        
        if ($user) {
            $this->recordSearch($query, 'users', $results->total(), $user);
        }

        return $results;
    }

    /**
     * Get search suggestions
     */
    public function getSearchSuggestions(string $query, User $user = null): array
    {
        $suggestions = [];

        // Get popular queries that start with the search term
        $popularQueries = SearchHistory::selectRaw('query, COUNT(*) as search_count')
            ->where('query', 'LIKE', "{$query}%")
            ->where('had_results', true)
            ->where('searched_at', '>=', now()->subDays(30))
            ->groupBy('query')
            ->orderByDesc('search_count')
            ->limit(5)
            ->pluck('query')
            ->toArray();

        $suggestions['popular'] = $popularQueries;

        // Get user's recent searches if logged in
        if ($user) {
            $userSuggestions = SearchHistory::getSuggestionsForUser($user, 3);
            $suggestions['recent'] = array_filter($userSuggestions, function ($suggestion) use ($query) {
                return stripos($suggestion, $query) !== false;
            });
        }

        return $suggestions;
    }

    /**
     * Get trending searches
     */
    public function getTrendingSearches(int $limit = 10): array
    {
        return SearchHistory::getPopularQueries($limit);
    }

    /**
     * Record search click
     */
    public function recordSearchClick(
        int $searchHistoryId,
        Model $result,
        int $position
    ): void {
        $searchHistory = SearchHistory::find($searchHistoryId);
        
        if ($searchHistory) {
            $searchHistory->recordClick($result, $position);
        }
    }

    /**
     * Get search analytics
     */
    public function getSearchAnalytics(array $filters = []): array
    {
        $query = SearchHistory::query();

        // Apply date filter
        if (isset($filters['date_from'])) {
            $query->where('searched_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('searched_at', '<=', $filters['date_to']);
        }

        // Apply search type filter
        if (isset($filters['search_type'])) {
            $query->where('search_type', $filters['search_type']);
        }

        return [
            'total_searches' => $query->count(),
            'unique_queries' => $query->distinct('query')->count(),
            'searches_with_results' => $query->where('had_results', true)->count(),
            'searches_without_results' => $query->where('had_results', false)->count(),
            'average_results_per_search' => $query->avg('results_count'),
            'most_popular_queries' => $query->selectRaw('query, COUNT(*) as count')
                ->groupBy('query')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'search_types_breakdown' => $query->selectRaw('search_type, COUNT(*) as count')
                ->groupBy('search_type')
                ->get(),
        ];
    }

    /**
     * Advanced search with multiple criteria
     */
    public function advancedSearch(array $criteria, int $perPage = 15, User $user = null): array
    {
        $results = [];

        // Search artworks with advanced criteria
        if (isset($criteria['artworks'])) {
            $artworkFilters = $criteria['artworks'];
            $query = $artworkFilters['query'] ?? '';
            unset($artworkFilters['query']);
            
            $results['artworks'] = $this->artworkRepository->search($query, $artworkFilters, $perPage);
        }

        // Search posts with advanced criteria
        if (isset($criteria['posts'])) {
            $postFilters = $criteria['posts'];
            $query = $postFilters['query'] ?? '';
            unset($postFilters['query']);
            
            $results['posts'] = $this->postRepository->search($query, $postFilters, $perPage);
        }

        // Search walls with advanced criteria
        if (isset($criteria['walls'])) {
            $wallFilters = $criteria['walls'];
            $query = $wallFilters['query'] ?? '';
            unset($wallFilters['query']);
            
            $results['walls'] = $this->wallRepository->search($query, $wallFilters, $perPage);
        }

        return $results;
    }

    /**
     * Record search in history
     */
    protected function recordSearch(
        string $query,
        string $searchType,
        int $resultsCount,
        User $user = null,
        array $filters = []
    ): void {
        SearchHistory::recordSearch(
            $query,
            $searchType,
            $resultsCount,
            $user,
            $filters,
            [
                'ip_address' => request()->ip(),
                'device_type' => $this->detectDeviceType(),
            ]
        );
    }

    /**
     * Detect device type from user agent
     */
    protected function detectDeviceType(): string
    {
        $userAgent = request()->userAgent();
        
        if (preg_match('/mobile|android|iphone|ipad/i', $userAgent)) {
            return 'mobile';
        }
        
        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }
}
