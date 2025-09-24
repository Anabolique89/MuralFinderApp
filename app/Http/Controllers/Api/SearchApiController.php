<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\ApiBaseController;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SearchApiController extends ApiBaseController
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Global search across all content types
     */
    public function global(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:255',
            'per_page' => 'nullable|integer|min:1|max:50',
            'category_id' => 'nullable|exists:categories,id',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $query = $request->input('query');
            $filters = $request->only(['category_id', 'location']);
            $perPage = $request->input('per_page', 15);
            $user = $request->user();

            $results = $this->searchService->globalSearch($query, $filters, $perPage, $user);

            return $this->sendSuccess($results, 'Search results retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Global search error: ' . $e->getMessage());
            return $this->sendError('Search failed', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search artworks
     */
    public function artworks(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:255',
            'per_page' => 'nullable|integer|min:1|max:50',
            'category_id' => 'nullable|exists:categories,id',
            'style' => 'nullable|string|max:100',
            'technique' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $query = $request->input('query');
            $filters = $request->only(['category_id', 'style', 'technique', 'location']);
            $perPage = $request->input('per_page', 15);
            $user = $request->user();

            $results = $this->searchService->searchArtworks($query, $filters, $perPage, $user);

            return $this->sendSuccess($results, 'Artwork search results retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Artwork search error: ' . $e->getMessage());
            return $this->sendError('Artwork search failed', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search posts
     */
    public function posts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:255',
            'per_page' => 'nullable|integer|min:1|max:50',
            'category_id' => 'nullable|exists:categories,id',
            'type' => ['nullable', Rule::in(['article', 'discussion', 'question', 'showcase', 'event', 'news'])],
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $query = $request->input('query');
            $filters = $request->only(['category_id', 'type', 'user_id']);
            $perPage = $request->input('per_page', 15);
            $user = $request->user();

            $results = $this->searchService->searchPosts($query, $filters, $perPage, $user);

            return $this->sendSuccess($results, 'Post search results retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Post search error: ' . $e->getMessage());
            return $this->sendError('Post search failed', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search walls
     */
    public function walls(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:255',
            'per_page' => 'nullable|integer|min:1|max:50',
            'wall_type' => ['nullable', Rule::in(['building', 'fence', 'tunnel', 'bridge', 'other'])],
            'surface_type' => ['nullable', Rule::in(['brick', 'concrete', 'metal', 'wood', 'other'])],
            'is_legal' => 'nullable|boolean',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $query = $request->input('query');
            $filters = $request->only(['wall_type', 'surface_type', 'is_legal', 'city', 'country']);
            $perPage = $request->input('per_page', 15);
            $user = $request->user();

            $results = $this->searchService->searchWalls($query, $filters, $perPage, $user);

            return $this->sendSuccess($results, 'Wall search results retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Wall search error: ' . $e->getMessage());
            return $this->sendError('Wall search failed', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search users
     */
    public function users(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:255',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $query = $request->input('query');
            $perPage = $request->input('per_page', 15);
            $user = $request->user();

            $results = $this->searchService->searchUsers($query, $perPage, $user);

            return $this->sendSuccess($results, 'User search results retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('User search error: ' . $e->getMessage());
            return $this->sendError('User search failed', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get search suggestions
     */
    public function suggestions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:1|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $query = $request->input('query');
            $user = $request->user();

            $suggestions = $this->searchService->getSearchSuggestions($query, $user);

            return $this->sendSuccess($suggestions, 'Search suggestions retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Search suggestions error: ' . $e->getMessage());
            return $this->sendError('Failed to get search suggestions', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get trending searches
     */
    public function trending(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $trending = $this->searchService->getTrendingSearches($limit);

            return $this->sendSuccess($trending, 'Trending searches retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Trending searches error: ' . $e->getMessage());
            return $this->sendError('Failed to get trending searches', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Record search click
     */
    public function recordClick(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search_history_id' => 'required|exists:search_history,id',
            'result_type' => 'required|string|in:artwork,post,wall,user',
            'result_id' => 'required|integer',
            'position' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $searchHistoryId = $request->input('search_history_id');
            $resultType = $request->input('result_type');
            $resultId = $request->input('result_id');
            $position = $request->input('position');

            // Get the result model
            $modelClass = match($resultType) {
                'artwork' => \App\Models\Artwork::class,
                'post' => \App\Models\Post::class,
                'wall' => \App\Models\Wall::class,
                'user' => \App\Models\User::class,
            };

            $result = $modelClass::findOrFail($resultId);

            $this->searchService->recordSearchClick($searchHistoryId, $result, $position);

            return $this->sendSuccess(null, 'Search click recorded successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError('Result not found', JsonResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            logger()->error('Search click recording error: ' . $e->getMessage());
            return $this->sendError('Failed to record search click', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Advanced search with multiple criteria
     */
    public function advanced(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'artworks' => 'nullable|array',
            'artworks.query' => 'nullable|string|min:2',
            'artworks.category_id' => 'nullable|exists:categories,id',
            'artworks.style' => 'nullable|string',
            'artworks.technique' => 'nullable|string',
            'posts' => 'nullable|array',
            'posts.query' => 'nullable|string|min:2',
            'posts.category_id' => 'nullable|exists:categories,id',
            'posts.type' => ['nullable', Rule::in(['article', 'discussion', 'question', 'showcase', 'event', 'news'])],
            'walls' => 'nullable|array',
            'walls.query' => 'nullable|string|min:2',
            'walls.wall_type' => ['nullable', Rule::in(['building', 'fence', 'tunnel', 'bridge', 'other'])],
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $criteria = $request->only(['artworks', 'posts', 'walls']);
            $perPage = $request->input('per_page', 15);
            $user = $request->user();

            $results = $this->searchService->advancedSearch($criteria, $perPage, $user);

            return $this->sendSuccess($results, 'Advanced search results retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Advanced search error: ' . $e->getMessage());
            return $this->sendError('Advanced search failed', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
