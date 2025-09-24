<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\ApiBaseController;
use App\Services\ArtworkService;
use App\Services\CommentService;
use App\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ArtworkApiController extends ApiBaseController
{
    protected ArtworkService $artworkService;
    protected CommentService $commentService;
    protected CategoryRepository $categoryRepository;

    public function __construct(
        ArtworkService $artworkService,
        CommentService $commentService,
        CategoryRepository $categoryRepository
    ) {
        $this->artworkService = $artworkService;
        $this->commentService = $commentService;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get artworks feed
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['category_id', 'style', 'technique', 'location']);
            $perPage = $request->input('per_page', 15);

            $artworks = $this->artworkService->getArtworkFeed($filters, $perPage);

            return $this->sendSuccess($artworks, 'Artworks retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Artwork index error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve artworks', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get single artwork
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $artwork = $this->artworkService->getArtworkById($id, $request->user());

            if (!$artwork) {
                return $this->sendError('Artwork not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($artwork, 'Artwork retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Artwork show error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve artwork', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create new artwork
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'wall_id' => 'nullable|exists:walls,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            'tags' => 'nullable|array',
            'style' => 'nullable|in:graffiti,mural,stencil,mosaic,sculpture,installation,other',
            'technique' => 'nullable|in:spray_paint,brush,marker,stencil,digital,mixed_media,other',
            'created_date' => 'nullable|date',
            'is_commissioned' => 'nullable|in:0,1,true,false',
            'commissioner' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_text' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $artworkData = $request->only([
                'title', 'description', 'category_id', 'wall_id', 'tags', 'style',
                'technique', 'created_date', 'is_commissioned', 'commissioner',
                'latitude', 'longitude', 'location_text'
            ]);

            $images = $request->file('images', []);

            $artwork = $this->artworkService->createArtwork($user, $artworkData, $images);

            return $this->sendSuccess($artwork, 'Artwork created successfully', JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            logger()->error('Artwork creation error: ' . $e->getMessage());
            return $this->sendError('Failed to create artwork', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update artwork
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'wall_id' => 'nullable|exists:walls,id',
            'tags' => 'nullable|array',
            'style' => 'nullable|string|max:100',
            'technique' => 'nullable|string|max:100',
            'created_date' => 'nullable|date',
            'is_commissioned' => 'boolean',
            'commissioner' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_text' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $artworkData = $request->only([
                'title', 'description', 'category_id', 'wall_id', 'tags', 'style',
                'technique', 'created_date', 'is_commissioned', 'commissioner',
                'latitude', 'longitude', 'location_text'
            ]);

            $updatedArtwork = $this->artworkService->updateArtworkById($id, $user, $artworkData);

            if (!$updatedArtwork) {
                return $this->sendError('Artwork not found or unauthorized', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($updatedArtwork, 'Artwork updated successfully');
        } catch (\Exception $e) {
            logger()->error('Artwork update error: ' . $e->getMessage());
            return $this->sendError('Failed to update artwork', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete artwork
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $success = $this->artworkService->deleteArtworkById($id, $user);

            if (!$success) {
                return $this->sendError('Artwork not found or unauthorized', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess(null, 'Artwork deleted successfully');
        } catch (\Exception $e) {
            logger()->error('Artwork deletion error: ' . $e->getMessage());
            return $this->sendError('Failed to delete artwork', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Like/Unlike artwork
     */
    public function toggleLike(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->artworkService->toggleLike($id, $user);

            if (!$result) {
                return $this->sendError('Artwork not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($result, $result['message']);
        } catch (\Exception $e) {
            logger()->error('Artwork like toggle error: ' . $e->getMessage());
            return $this->sendError('Failed to toggle like', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get artwork comments
     */
    public function comments(Request $request, int $id): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 20);
            $comments = $this->artworkService->getArtworkComments($id, $perPage);

            if ($comments === null) {
                return $this->sendError('Artwork not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($comments, 'Comments retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Artwork comments error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve comments', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add comment to artwork
     */
    public function addComment(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $comment = $this->artworkService->addComment(
                $id,
                $user,
                $request->content,
                $request->parent_id
            );

            if (!$comment) {
                return $this->sendError('Artwork not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($comment, 'Comment added successfully', JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            logger()->error('Artwork comment creation error: ' . $e->getMessage());
            return $this->sendError('Failed to add comment', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search artworks
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'category_id' => 'nullable|exists:categories,id',
            'style' => 'nullable|string',
            'technique' => 'nullable|string',
            'location' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $query = $request->input('query');
            $filters = $request->only(['category_id', 'style', 'technique', 'location']);
            $perPage = $request->input('per_page', 15);

            $artworks = $this->artworkService->searchArtworks($query, $filters, $perPage);

            return $this->sendSuccess($artworks, 'Search results retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Artwork search error: ' . $e->getMessage());
            return $this->sendError('Search failed', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get featured artworks
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $artworks = $this->artworkService->getFeaturedArtworks($limit);

            return $this->sendSuccess($artworks, 'Featured artworks retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Featured artworks error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve featured artworks', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get nearby artworks
     */
    public function nearby(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $radius = $request->input('radius', 10);

            $artworks = $this->artworkService->getNearbyArtworks($latitude, $longitude, $radius);

            return $this->sendSuccess($artworks, 'Nearby artworks retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Nearby artworks error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve nearby artworks', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
