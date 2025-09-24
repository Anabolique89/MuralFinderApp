<?php

namespace App\Services;

use App\Models\Wall;
use App\Models\User;
use App\Models\WallCheckIn;
use App\Repositories\WallRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class WallService
{
    protected WallRepository $wallRepository;
    protected ImageService $imageService;
    protected NotificationService $notificationService;

    public function __construct(
        WallRepository $wallRepository,
        ImageService $imageService,
        NotificationService $notificationService
    ) {
        $this->wallRepository = $wallRepository;
        $this->imageService = $imageService;
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new wall
     */
    public function createWall(User $user, array $wallData, array $images = []): Wall
    {
        return DB::transaction(function () use ($user, $wallData, $images) {
            // Upload images
            $uploadedImages = [];
            $primaryImage = null;

            if (!empty($images)) {
                foreach ($images as $index => $image) {
                    $imagePath = $this->imageService->uploadWallImage($image, $user->id);
                    $uploadedImages[] = $imagePath;

                    if ($index === 0) {
                        $primaryImage = $imagePath;
                    }
                }
            }

            // Create wall
            $wall = $this->wallRepository->create([
                'name' => $wallData['name'] ?? null,
                'description' => $wallData['description'] ?? null,
                'location_text' => $wallData['location_text'],
                'address' => $wallData['address'] ?? null,
                'city' => $wallData['city'] ?? null,
                'country' => $wallData['country'] ?? null,
                'latitude' => $wallData['latitude'],
                'longitude' => $wallData['longitude'],
                'image_path' => $primaryImage,
                'images' => $uploadedImages,
                'added_by' => $user->id,
                'status' => Wall::STATUS_PENDING,
                'wall_type' => $wallData['wall_type'] ?? null,
                'surface_type' => $wallData['surface_type'] ?? null,
                'height' => $wallData['height'] ?? null,
                'width' => $wallData['width'] ?? null,
                'is_legal' => $wallData['is_legal'] ?? null,
                'requires_permission' => $wallData['requires_permission'] ?? true,
            ]);

            // Notify nearby users about new wall
            $this->notifyNearbyUsers($wall);

            return $wall->load(['addedBy.profile']);
        });
    }

    /**
     * Update wall
     */
    public function updateWall(Wall $wall, array $wallData): Wall
    {
        $this->wallRepository->update($wall, $wallData);
        return $wall->fresh(['addedBy.profile', 'verifiedBy']);
    }

    /**
     * Verify wall
     */
    public function verifyWall(Wall $wall, User $verifier): bool
    {
        $wall->verify($verifier);
        return true;
    }

    /**
     * Reject wall
     */
    public function rejectWall(Wall $wall, User $verifier, string $reason): bool
    {
        $wall->reject($verifier, $reason);
        return true;
    }

    /**
     * Delete wall
     */
    public function deleteWall(Wall $wall): bool
    {
        return DB::transaction(function () use ($wall) {
            // Delete images
            if ($wall->image_path) {
                $this->imageService->deleteImageSizes($wall->image_path);
            }

            if ($wall->images) {
                foreach ($wall->images as $imagePath) {
                    $this->imageService->deleteImageSizes($imagePath);
                }
            }

            return $this->wallRepository->delete($wall);
        });
    }

    /**
     * Check in to a wall
     */
    public function checkInToWall(
        Wall $wall,
        User $user,
        array $checkInData
    ): WallCheckIn {
        $checkIn = WallCheckIn::create([
            'user_id' => $user->id,
            'wall_id' => $wall->id,
            'note' => $checkInData['note'] ?? null,
            'images' => $checkInData['images'] ?? [],
            'latitude' => $checkInData['latitude'],
            'longitude' => $checkInData['longitude'],
            'accuracy' => $checkInData['accuracy'] ?? null,
            'visit_purpose' => $checkInData['visit_purpose'] ?? null,
            'duration_minutes' => $checkInData['duration_minutes'] ?? null,
            'companions' => $checkInData['companions'] ?? [],
            'is_public' => $checkInData['is_public'] ?? true,
        ]);

        // Verify check-in location
        $checkIn->verify();

        // Update wall check-ins count
        $wall->incrementCheckInsCount();

        return $checkIn;
    }

    /**
     * Like wall
     */
    public function likeWall(Wall $wall, User $user): bool
    {
        if ($wall->likes()->where('user_id', $user->id)->exists()) {
            return false;
        }

        $wall->likes()->create([
            'user_id' => $user->id,
            'reaction_type' => 'like',
        ]);

        $wall->incrementLikesCount();

        // Create notification
        if ($wall->added_by !== $user->id) {
            $this->notificationService->createWallLikedNotification($wall, $user);
        }

        return true;
    }

    /**
     * Unlike wall
     */
    public function unlikeWall(Wall $wall, User $user): bool
    {
        $like = $wall->likes()->where('user_id', $user->id)->first();

        if (!$like) {
            return false;
        }

        $like->delete();
        $wall->decrement('likes_count');

        return true;
    }

    /**
     * Search walls
     */
    public function searchWalls(string $query, array $filters = [], int $perPage = 15)
    {
        return $this->wallRepository->search($query, $filters, $perPage);
    }

    /**
     * Get nearby walls
     */
    public function getNearbyWalls(float $latitude, float $longitude, float $radius = 10)
    {
        return $this->wallRepository->getNearby($latitude, $longitude, $radius);
    }

    /**
     * Get verified walls
     */
    public function getVerifiedWalls(int $perPage = 15)
    {
        return $this->wallRepository->getVerified($perPage);
    }

    /**
     * Get pending walls for moderation
     */
    public function getPendingWalls(int $perPage = 15)
    {
        return $this->wallRepository->getPending($perPage);
    }

    /**
     * Get walls by user
     */
    public function getUserWalls(User $user, int $perPage = 15)
    {
        return $this->wallRepository->getByUser($user, $perPage);
    }

    /**
     * Get wall statistics
     */
    public function getWallStats(Wall $wall): array
    {
        return [
            'artworks_count' => $wall->artworks_count,
            'likes_count' => $wall->likes_count,
            'comments_count' => $wall->comments_count,
            'check_ins_count' => $wall->check_ins_count,
            'views_count' => $wall->views_count,
            'unique_visitors' => $wall->checkIns()->distinct('user_id')->count(),
        ];
    }

    /**
     * Notify nearby users about new wall
     */
    protected function notifyNearbyUsers(Wall $wall): void
    {
        // Find users within 5km who have location enabled
        $nearbyUsers = User::whereHas('profile', function ($query) use ($wall) {
            $query->whereNotNull('latitude')
                  ->whereNotNull('longitude')
                  ->where('show_location', true);
        })->get();

        foreach ($nearbyUsers as $user) {
            $distance = $this->calculateDistance(
                $wall->latitude,
                $wall->longitude,
                $user->profile->latitude,
                $user->profile->longitude
            );

            if ($distance <= 5) { // 5km radius
                $this->notificationService->createNearbyWallNotification($user, $wall);
            }
        }
    }

    /**
     * Calculate distance between two points in kilometers
     */
    protected function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get wall by ID
     */
    public function getWallById(int $id): ?Wall
    {
        return $this->wallRepository->findById($id);
    }

    /**
     * Update wall by ID with ownership check
     */
    public function updateWallById(int $id, User $user, array $data): ?Wall
    {
        $wall = $this->wallRepository->findById($id);

        if (!$wall || $wall->added_by !== $user->id) {
            return null;
        }

        return $this->updateWall($wall, $data);
    }

    /**
     * Delete wall by ID with ownership check
     */
    public function deleteWallById(int $id, User $user): bool
    {
        $wall = $this->wallRepository->findById($id);

        if (!$wall || $wall->added_by !== $user->id) {
            return false;
        }

        $this->deleteWall($wall);
        return true;
    }

    /**
     * Toggle like for wall by ID
     */
    public function toggleLike(int $id, User $user): ?array
    {
        $wall = $this->wallRepository->findById($id);

        if (!$wall) {
            return null;
        }

        $isLiked = $wall->likes()->where('user_id', $user->id)->exists();

        if ($isLiked) {
            $this->unlikeWall($wall, $user);
            $message = 'Wall unliked successfully';
        } else {
            $this->likeWall($wall, $user);
            $message = 'Wall liked successfully';
        }

        return [
            'liked' => !$isLiked,
            'likes_count' => $wall->fresh()->likes_count,
            'message' => $message,
        ];
    }

    /**
     * Check in to wall by ID
     */
    public function checkInToWallById(int $id, User $user, array $checkInData): ?object
    {
        $wall = $this->wallRepository->findById($id);

        if (!$wall) {
            return null;
        }

        return $this->checkInToWall($wall, $user, $checkInData);
    }

    /**
     * Get wall comments
     */
    public function getWallComments(int $id, int $perPage = 20)
    {
        $wall = $this->wallRepository->findById($id);

        if (!$wall) {
            return null;
        }

        return $wall->comments()
            ->with(['user.profile'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Add comment to wall
     */
    public function addComment(int $id, User $user, string $content, ?int $parentId = null): ?object
    {
        $wall = $this->wallRepository->findById($id);

        if (!$wall) {
            return null;
        }

        $comment = $wall->comments()->create([
            'user_id' => $user->id,
            'content' => $content,
            'parent_id' => $parentId,
            'status' => 'published',
        ]);

        // Increment comments count
        $wall->increment('comments_count');

        // Create notification for wall owner
        if ($wall->added_by !== $user->id) {
            $this->notificationService->createWallCommentedNotification($wall, $comment);
        }

        return $comment->load(['user.profile']);
    }
}
