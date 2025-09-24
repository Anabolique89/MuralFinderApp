<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\ApiBaseController;
use App\Services\UserService;
use App\Services\ArtworkService;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserApiController extends ApiBaseController
{
    protected UserService $userService;
    protected ArtworkService $artworkService;
    protected PostService $postService;

    public function __construct(UserService $userService, ArtworkService $artworkService, PostService $postService)
    {
        $this->userService = $userService;
        $this->artworkService = $artworkService;
        $this->postService = $postService;
    }

    /**
     * Get current user profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load('profile');
            return $this->sendSuccess($user, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Profile retrieval error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve profile', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'date_of_birth' => 'nullable|date|before:today',
            'profession' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'website' => 'nullable|url|max:255',
            'instagram' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'is_profile_public' => 'boolean',
            'show_location' => 'boolean',
            'show_email' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $profileData = $request->only([
                'first_name', 'last_name', 'bio', 'date_of_birth', 'profession',
                'country', 'city', 'location', 'latitude', 'longitude',
                'website', 'instagram', 'twitter', 'facebook', 'linkedin', 'tiktok',
                'is_profile_public', 'show_location', 'show_email'
            ]);

            $profile = $this->userService->updateProfile($user, $profileData);

            return $this->sendSuccess($profile, 'Profile updated successfully');
        } catch (\Exception $e) {
            logger()->error('Profile update error: ' . $e->getMessage());
            return $this->sendError('Failed to update profile', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Upload profile image
     */
    public function uploadProfileImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $image = $request->file('image');

            $imagePath = $this->userService->uploadProfileImage($user, $image);

            return $this->sendSuccess(['image_url' => $imagePath], 'Profile image uploaded successfully');
        } catch (\Exception $e) {
            logger()->error('Profile image upload error: ' . $e->getMessage());
            return $this->sendError('Failed to upload profile image', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user by username
     */
    public function show(Request $request, string $username): JsonResponse
    {
        try {
            $user = $this->userService->getUserByUsername($username);

            if (!$user) {
                return $this->sendError('User not found', JsonResponse::HTTP_NOT_FOUND);
            }

            $user->load('profile');

            // Get user statistics
            $stats = $this->userService->getUserStats($user);

            return $this->sendSuccess([
                'user' => $user,
                'stats' => $stats
            ], 'User retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('User show error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve user', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Follow/Unfollow user
     */
    public function toggleFollow(Request $request, string $username): JsonResponse
    {
        try {
            $userToFollow = $this->userService->getUserByUsername($username);

            if (!$userToFollow) {
                return $this->sendError('User not found', JsonResponse::HTTP_NOT_FOUND);
            }

            $currentUser = $request->user();

            if ($currentUser->id === $userToFollow->id) {
                return $this->sendError('Cannot follow yourself', JsonResponse::HTTP_BAD_REQUEST);
            }

            $isFollowing = $currentUser->isFollowing($userToFollow);

            if ($isFollowing) {
                $this->userService->unfollowUser($currentUser, $userToFollow);
                $message = 'User unfollowed successfully';
            } else {
                $this->userService->followUser($currentUser, $userToFollow);
                $message = 'User followed successfully';
            }

            return $this->sendSuccess([
                'following' => !$isFollowing,
                'followers_count' => $userToFollow->fresh()->profile->followers_count ?? 0
            ], $message);
        } catch (\Exception $e) {
            logger()->error('Follow toggle error: ' . $e->getMessage());
            return $this->sendError('Failed to toggle follow', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user's artworks
     */
    public function artworks(Request $request, string $username): JsonResponse
    {
        try {
            $user = $this->userService->getUserByUsername($username);

            if (!$user) {
                return $this->sendError('User not found', JsonResponse::HTTP_NOT_FOUND);
            }

            $perPage = $request->input('per_page', 15);
            $userArtworks = $this->artworkService->getUserArtworks($user, $perPage);

            return $this->sendSuccess($userArtworks, 'User artworks retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('User artworks error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve user artworks', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user's walls
     */
    public function walls(Request $request, string $username): JsonResponse
    {
        try {
            $user = $this->userService->getUserByUsername($username);

            if (!$user) {
                return $this->sendError('User not found', JsonResponse::HTTP_NOT_FOUND);
            }

            $perPage = $request->input('per_page', 15);
            $userWalls = $this->userService->getUserWalls($user, $perPage);

            return $this->sendSuccess($userWalls, 'User walls retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('User walls error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve user walls', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user's posts
     */
    public function posts(Request $request, string $username): JsonResponse
    {
        try {
            $user = $this->userService->getUserByUsername($username);

            if (!$user) {
                return $this->sendError('User not found', JsonResponse::HTTP_NOT_FOUND);
            }

            $perPage = $request->input('per_page', 15);
            $userPosts = $this->postService->getUserPosts($user, $perPage);

            return $this->sendSuccess($userPosts, 'User posts retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('User posts error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve user posts', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user's followers
     */
    public function followers(Request $request, string $username): JsonResponse
    {
        try {
            $user = $this->userService->getUserByUsername($username);

            if (!$user) {
                return $this->sendError('User not found', JsonResponse::HTTP_NOT_FOUND);
            }

            $perPage = $request->input('per_page', 20);
            $followers = $user->followers()->with('profile')->paginate($perPage);

            return $this->sendSuccess($followers, 'Followers retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('User followers error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve followers', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user's following
     */
    public function following(Request $request, string $username): JsonResponse
    {
        try {
            $user = $this->userService->getUserByUsername($username);

            if (!$user) {
                return $this->sendError('User not found', JsonResponse::HTTP_NOT_FOUND);
            }

            $perPage = $request->input('per_page', 20);
            $following = $user->following()->with('profile')->paginate($perPage);

            return $this->sendSuccess($following, 'Following retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('User following error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve following', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search users
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $query = $request->input('query');
            $perPage = $request->input('per_page', 15);

            $users = $this->userService->searchUsers($query, $perPage);

            return $this->sendSuccess($users, 'Search results retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('User search error: ' . $e->getMessage());
            return $this->sendError('Search failed', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user activity feed
     */
    public function activityFeed(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 20);

            $feed = $this->userService->getUserActivityFeed($user, $perPage);

            return $this->sendSuccess($feed, 'Activity feed retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Activity feed error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve activity feed', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();

            // Verify current password
            if (!\Hash::check($request->current_password, $user->password)) {
                return $this->sendError('Current password is incorrect', JsonResponse::HTTP_BAD_REQUEST);
            }

            $this->userService->changePassword($user, $request->new_password);

            return $this->sendSuccess(null, 'Password changed successfully');
        } catch (\Exception $e) {
            logger()->error('Password change error: ' . $e->getMessage());
            return $this->sendError('Failed to change password', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Deactivate account
     */
    public function deactivateAccount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $this->userService->deactivateUser($user);

            return $this->sendSuccess(null, 'Account deactivated successfully');
        } catch (\Exception $e) {
            logger()->error('Account deactivation error: ' . $e->getMessage());
            return $this->sendError('Failed to deactivate account', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
