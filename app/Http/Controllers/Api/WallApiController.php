<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\ApiBaseController;
use App\Services\WallService;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WallApiController extends ApiBaseController
{
    protected WallService $wallService;
    protected CommentService $commentService;

    public function __construct(WallService $wallService, CommentService $commentService)
    {
        $this->wallService = $wallService;
        $this->commentService = $commentService;
    }

    /**
     * Get walls
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $walls = $this->wallService->getVerifiedWalls($perPage);

            return $this->sendSuccess($walls, 'Walls retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Wall index error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve walls', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get single wall
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $wall = $this->wallService->getWallById($id);

            if (!$wall) {
                return $this->sendError('Wall not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($wall, 'Wall retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Wall show error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve wall', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create new wall
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'location_text' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            'wall_type' => ['nullable', Rule::in(['building', 'fence', 'tunnel', 'bridge', 'other'])],
            'surface_type' => ['nullable', Rule::in(['brick', 'concrete', 'metal', 'wood', 'other'])],
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'is_legal' => 'nullable|boolean',
            'requires_permission' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $wallData = $request->only([
                'name', 'description', 'location_text', 'address', 'city', 'country',
                'latitude', 'longitude', 'wall_type', 'surface_type', 'height', 'width',
                'is_legal', 'requires_permission'
            ]);

            $images = $request->file('images', []);

            $wall = $this->wallService->createWall($user, $wallData, $images);

            return $this->sendSuccess($wall, 'Wall submitted for verification', JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            logger()->error('Wall creation error: ' . $e->getMessage());
            return $this->sendError('Failed to create wall', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update wall
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|nullable|string|max:255',
            'description' => 'nullable|string',
            'location_text' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'wall_type' => ['nullable', Rule::in(['building', 'fence', 'tunnel', 'bridge', 'other'])],
            'surface_type' => ['nullable', Rule::in(['brick', 'concrete', 'metal', 'wood', 'other'])],
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'is_legal' => 'nullable|boolean',
            'requires_permission' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $wallData = $request->only([
                'name', 'description', 'location_text', 'address', 'city', 'country',
                'latitude', 'longitude', 'wall_type', 'surface_type', 'height', 'width',
                'is_legal', 'requires_permission'
            ]);

            $updatedWall = $this->wallService->updateWallById($id, $user, $wallData);

            if (!$updatedWall) {
                return $this->sendError('Wall not found or unauthorized', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($updatedWall, 'Wall updated successfully');
        } catch (\Exception $e) {
            logger()->error('Wall update error: ' . $e->getMessage());
            return $this->sendError('Failed to update wall', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete wall
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $success = $this->wallService->deleteWallById($id, $user);

            if (!$success) {
                return $this->sendError('Wall not found or unauthorized', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess(null, 'Wall deleted successfully');
        } catch (\Exception $e) {
            logger()->error('Wall deletion error: ' . $e->getMessage());
            return $this->sendError('Failed to delete wall', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Like/Unlike wall
     */
    public function toggleLike(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->wallService->toggleLike($id, $user);

            if (!$result) {
                return $this->sendError('Wall not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($result, $result['message']);
        } catch (\Exception $e) {
            logger()->error('Wall like toggle error: ' . $e->getMessage());
            return $this->sendError('Failed to toggle like', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get wall comments
     */
    public function comments(Request $request, int $id): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 20);
            $comments = $this->wallService->getWallComments($id, $perPage);

            if ($comments === null) {
                return $this->sendError('Wall not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($comments, 'Comments retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Wall comments error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve comments', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add comment to wall
     */
    public function addComment(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $comment = $this->wallService->addComment(
                $id,
                $user,
                $request->comment,
                $request->parent_id
            );

            if (!$comment) {
                return $this->sendError('Wall not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($comment, 'Comment added successfully', JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            logger()->error('Wall comment creation error: ' . $e->getMessage());
            return $this->sendError('Failed to add comment', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check in to wall
     */
    public function checkIn(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'note' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'visit_purpose' => ['nullable', Rule::in(['viewing', 'painting', 'photography', 'maintenance', 'other'])],
            'duration_minutes' => 'nullable|integer|min:1',
            'companions' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $checkInData = $request->only([
                'note', 'images', 'latitude', 'longitude', 'accuracy',
                'visit_purpose', 'duration_minutes', 'companions', 'is_public'
            ]);

            $checkIn = $this->wallService->checkInToWallById($id, $user, $checkInData);

            if (!$checkIn) {
                return $this->sendError('Wall not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($checkIn, 'Check-in successful', JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            logger()->error('Wall check-in error: ' . $e->getMessage());
            return $this->sendError('Check-in failed', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search walls
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'wall_type' => ['nullable', Rule::in(['building', 'fence', 'tunnel', 'bridge', 'other'])],
            'surface_type' => ['nullable', Rule::in(['brick', 'concrete', 'metal', 'wood', 'other'])],
            'is_legal' => 'nullable|boolean',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $query = $request->input('query');
            $filters = $request->only(['wall_type', 'surface_type', 'is_legal', 'city', 'country']);
            $perPage = $request->input('per_page', 15);

            $walls = $this->wallService->searchWalls($query, $filters, $perPage);

            return $this->sendSuccess($walls, 'Search results retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Wall search error: ' . $e->getMessage());
            return $this->sendError('Search failed', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get nearby walls
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

            $walls = $this->wallService->getNearbyWalls($latitude, $longitude, $radius);

            return $this->sendSuccess($walls, 'Nearby walls retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Nearby walls error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve nearby walls', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
