<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class UserService
{
    protected UserRepository $userRepository;
    protected ImageService $imageService;

    public function __construct(UserRepository $userRepository, ImageService $imageService)
    {
        $this->userRepository = $userRepository;
        $this->imageService = $imageService;
    }

    /**
     * Create a new user with profile
     */
    public function createUser(array $userData, array $profileData = []): User
    {
        return DB::transaction(function () use ($userData, $profileData) {
            // Create user
            $user = $this->userRepository->create([
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => $userData['role'] ?? User::ROLE_ARTLOVER,
                'status' => User::STATUS_ACTIVE,
            ]);

            // Create profile
            $user->profile()->create(array_merge([
                'first_name' => $profileData['first_name'] ?? null,
                'last_name' => $profileData['last_name'] ?? null,
                'bio' => $profileData['bio'] ?? null,
                'date_of_birth' => $profileData['date_of_birth'] ?? null,
                'profession' => $profileData['profession'] ?? null,
                'country' => $profileData['country'] ?? null,
                'city' => $profileData['city'] ?? null,
            ], $profileData));

            // Create notification preferences
            $user->notificationPreferences()->create(
                \App\Models\NotificationPreference::getDefaults()
            );

            return $user->load('profile');
        });
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $profileData): UserProfile
    {
        $profile = $user->profile;

        if (!$profile) {
            $profile = $user->profile()->create($profileData);
        } else {
            $profile->update($profileData);
        }

        return $profile->fresh();
    }

    /**
     * Upload profile image
     */
    public function uploadProfileImage(User $user, UploadedFile $image): string
    {
        $imagePath = $this->imageService->uploadProfileImage($image, $user->id);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['profile_image_url' => $imagePath]
        );

        return $imagePath;
    }

    /**
     * Follow a user
     */
    public function followUser(User $follower, User $following): bool
    {
        if ($follower->id === $following->id) {
            return false;
        }

        if ($follower->isFollowing($following)) {
            return false;
        }

        $follower->follow($following);

        // Update counts
        $follower->profile?->incrementFollowingCount();
        $following->profile?->incrementFollowersCount();

        // Create notification
        app(NotificationService::class)->createFollowNotification($following, $follower);

        return true;
    }

    /**
     * Unfollow a user
     */
    public function unfollowUser(User $follower, User $following): bool
    {
        if (!$follower->isFollowing($following)) {
            return false;
        }

        $follower->unfollow($following);

        // Update counts
        $follower->profile?->decrementFollowingCount();
        $following->profile?->decrementFollowersCount();

        return true;
    }

    /**
     * Search users
     */
    public function searchUsers(string $query, int $perPage = 15)
    {
        return $this->userRepository->search($query, $perPage);
    }

    /**
     * Get user by username
     */
    public function getUserByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }

    /**
     * Get user by email
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Update last login
     */
    public function updateLastLogin(User $user): void
    {
        $this->userRepository->updateLastLogin($user);
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $newPassword): bool
    {
        return $this->userRepository->update($user, [
            'password' => Hash::make($newPassword)
        ]);
    }

    /**
     * Deactivate user account
     */
    public function deactivateUser(User $user): bool
    {
        return $this->userRepository->update($user, [
            'status' => User::STATUS_INACTIVE
        ]);
    }

    /**
     * Activate user account
     */
    public function activateUser(User $user): bool
    {
        return $this->userRepository->update($user, [
            'status' => User::STATUS_ACTIVE
        ]);
    }

    /**
     * Get user statistics
     */
    public function getUserStats(User $user): array
    {
        return [
            'artworks_count' => $user->artworks()->count(),
            'posts_count' => $user->posts()->count(),
            'walls_count' => $user->walls()->count(),
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
            'likes_given' => $user->likes()->count(),
            'comments_made' => $user->comments()->count(),
        ];
    }

    /**
     * Get user activity feed
     */
    public function getUserActivityFeed(User $user, int $perPage = 20)
    {
        // Get activities from users they follow
        $followingIds = $user->following()->pluck('users.id');

        // This would need to be implemented based on your activity tracking needs
        // For now, return recent artworks and posts from followed users
        $artworks = \App\Models\Artwork::whereIn('user_id', $followingIds)
            ->published()
            ->with(['user.profile', 'category'])
            ->latest()
            ->take(10)
            ->get();

        $posts = \App\Models\Post::whereIn('user_id', $followingIds)
            ->published()
            ->with(['user.profile', 'category'])
            ->latest()
            ->take(10)
            ->get();

        return [
            'artworks' => $artworks,
            'posts' => $posts,
        ];
    }

    /**
     * Get user's walls
     */
    public function getUserWalls(User $user, int $perPage = 15): object
    {
        return app(\App\Repositories\WallRepository::class)->getByUser($user, $perPage);
    }
}
