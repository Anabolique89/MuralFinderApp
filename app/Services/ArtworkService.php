<?php

namespace App\Services;

use App\Models\Artwork;
use App\Models\User;
use App\Models\ArtworkView;
use App\Repositories\ArtworkRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ArtworkService
{
    protected ArtworkRepository $artworkRepository;
    protected ImageService $imageService;
    protected NotificationService $notificationService;

    public function __construct(
        ArtworkRepository $artworkRepository,
        ImageService $imageService,
        NotificationService $notificationService
    ) {
        $this->artworkRepository = $artworkRepository;
        $this->imageService = $imageService;
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new artwork
     */
    public function createArtwork(User $user, array $artworkData, array $images = []): Artwork
    {
        return DB::transaction(function () use ($user, $artworkData, $images) {
            // Generate slug
            $slug = $this->generateUniqueSlug($artworkData['title']);

            // Upload images
            $uploadedImages = [];
            $primaryImage = null;

            if (!empty($images)) {
                foreach ($images as $index => $image) {
                    $imagePath = $this->imageService->uploadArtworkImage($image, $user->id);
                    $uploadedImages[] = $imagePath;

                    if ($index === 0) {
                        $primaryImage = $imagePath;
                    }
                }
            }

            // Create artwork
            $artwork = $this->artworkRepository->create([
                'user_id' => $user->id,
                'title' => $artworkData['title'],
                'description' => $artworkData['description'] ?? null,
                'category_id' => $artworkData['category_id'] ?? null,
                'wall_id' => $artworkData['wall_id'] ?? null,
                'primary_image_path' => $primaryImage,
                'images' => $uploadedImages,
                'tags' => $artworkData['tags'] ?? [],
                'style' => $artworkData['style'] ?? null,
                'technique' => $artworkData['technique'] ?? null,
                'created_date' => $artworkData['created_date'] ?? null,
                'is_commissioned' => filter_var($artworkData['is_commissioned'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'commissioner' => $artworkData['commissioner'] ?? null,
                'latitude' => $artworkData['latitude'] ?? null,
                'longitude' => $artworkData['longitude'] ?? null,
                'location_text' => $artworkData['location_text'] ?? null,
                'status' => Artwork::STATUS_PUBLISHED,
                'slug' => $slug,
            ]);

            // Update user's artwork count
            $user->profile?->incrementArtworksCount();

            // Update category count
            if ($artwork->category) {
                $artwork->category->incrementArtworksCount();
            }

            // Update wall count
            if ($artwork->wall) {
                $artwork->wall->incrementArtworksCount();
            }

            return $artwork->load(['user.profile', 'category', 'wall']);
        });
    }

    /**
     * Update artwork
     */
    public function updateArtwork(Artwork $artwork, array $artworkData): Artwork
    {
        // Update slug if title changed
        if (isset($artworkData['title']) && $artworkData['title'] !== $artwork->title) {
            $artworkData['slug'] = $this->generateUniqueSlug($artworkData['title'], $artwork->id);
        }

        $this->artworkRepository->update($artwork, $artworkData);

        return $artwork->fresh(['user.profile', 'category', 'wall']);
    }

    /**
     * Delete artwork
     */
    public function deleteArtwork(Artwork $artwork): bool
    {
        return DB::transaction(function () use ($artwork) {
            // Update counts
            $artwork->user->profile?->decrementArtworksCount();
            $artwork->category?->decrementArtworksCount();
            $artwork->wall?->decrementArtworksCount();

            // Delete images
            $this->imageService->deleteArtworkImages($artwork);

            return $this->artworkRepository->delete($artwork);
        });
    }

    /**
     * Like artwork
     */
    public function likeArtwork(Artwork $artwork, User $user): bool
    {
        if ($artwork->likes()->where('user_id', $user->id)->exists()) {
            return false;
        }

        $artwork->likes()->create([
            'user_id' => $user->id,
            'reaction_type' => 'like',
        ]);

        $artwork->increment('likes_count');

        // Create notification
        if ($artwork->user_id !== $user->id) {
            $this->notificationService->createArtworkLikedNotification($artwork, $user);
        }

        return true;
    }

    /**
     * Unlike artwork
     */
    public function unlikeArtwork(Artwork $artwork, User $user): bool
    {
        $like = $artwork->likes()->where('user_id', $user->id)->first();

        if (!$like) {
            return false;
        }

        $like->delete();
        $artwork->decrement('likes_count');

        return true;
    }

    /**
     * Record artwork view
     */
    public function recordView(Artwork $artwork, User $user = null, array $context = []): void
    {
        ArtworkView::recordView($artwork, $user, $context);

        // Update view count if it's a unique view
        if (!$user || !ArtworkView::where('artwork_id', $artwork->id)
            ->where('user_id', $user->id)
            ->where('is_unique_view', true)
            ->exists()) {
            $artwork->increment('views_count');
        }
    }

    /**
     * Get artwork feed
     */
    public function getArtworkFeed(array $filters = [], int $perPage = 15, string $sortBy = 'newest', string $sortOrder = 'desc')
    {
        if (!empty($filters)) {
            return $this->artworkRepository->search('', $filters, $perPage, $sortBy, $sortOrder);
        }

        return $this->artworkRepository->getPublished($perPage, $sortBy, $sortOrder);
    }

    /**
     * Get featured artworks
     */
    public function getFeaturedArtworks(int $limit = 10)
    {
        return $this->artworkRepository->getFeatured($limit);
    }

    /**
     * Search artworks
     */
    public function searchArtworks(string $query, array $filters = [], int $perPage = 15)
    {
        return $this->artworkRepository->search($query, $filters, $perPage);
    }

    /**
     * Get nearby artworks
     */
    public function getNearbyArtworks(float $latitude, float $longitude, float $radius = 10)
    {
        return $this->artworkRepository->getNearby($latitude, $longitude, $radius);
    }

    /**
     * Feature artwork
     */
    public function featureArtwork(Artwork $artwork): bool
    {
        $artwork->feature();
        return true;
    }

    /**
     * Unfeature artwork
     */
    public function unfeatureArtwork(Artwork $artwork): bool
    {
        $artwork->unfeature();
        return true;
    }

    /**
     * Generate unique slug
     */
    private function generateUniqueSlug(string $title, int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = Artwork::where('slug', $slug);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get artwork statistics
     */
    public function getArtworkStats(Artwork $artwork): array
    {
        return [
            'views_count' => $artwork->views_count,
            'likes_count' => $artwork->likes_count,
            'comments_count' => $artwork->comments_count,
            'shares_count' => $artwork->shares_count,
            'unique_views' => $artwork->views()->where('is_unique_view', true)->count(),
            'view_duration_avg' => $artwork->views()->whereNotNull('view_duration')->avg('view_duration'),
        ];
    }

    /**
     * Get artwork by ID with view recording
     */
    public function getArtworkById(int $id, ?User $viewer = null): ?Artwork
    {
        $artwork = $this->artworkRepository->findById($id);

        if ($artwork && $viewer) {
            $this->recordView($artwork, $viewer, [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return $artwork;
    }

    /**
     * Update artwork by ID with ownership check
     */
    public function updateArtworkById(int $id, User $user, array $data): ?Artwork
    {
        $artwork = $this->artworkRepository->findById($id);

        if (!$artwork || $artwork->user_id !== $user->id) {
            return null;
        }

        return $this->updateArtwork($artwork, $data);
    }

    /**
     * Delete artwork by ID with ownership check
     */
    public function deleteArtworkById(int $id, User $user): bool
    {
        $artwork = $this->artworkRepository->findById($id);

        if (!$artwork || $artwork->user_id !== $user->id) {
            return false;
        }

        $this->deleteArtwork($artwork);
        return true;
    }

    /**
     * Toggle like for artwork by ID
     */
    public function toggleLike(int $id, User $user): ?array
    {
        $artwork = $this->artworkRepository->findById($id);

        if (!$artwork) {
            return null;
        }

        $isLiked = $artwork->likes()->where('user_id', $user->id)->exists();

        if ($isLiked) {
            $this->unlikeArtwork($artwork, $user);
            $message = 'Artwork unliked successfully';
        } else {
            $this->likeArtwork($artwork, $user);
            $message = 'Artwork liked successfully';
        }

        return [
            'liked' => !$isLiked,
            'likes_count' => $artwork->fresh()->likes_count,
            'message' => $message,
        ];
    }

    /**
     * Get artwork comments
     */
    public function getArtworkComments(int $id, int $perPage = 20): ?object
    {
        $artwork = $this->artworkRepository->findById($id);

        if (!$artwork) {
            return null;
        }

        return app(CommentService::class)->getCommentsForModel($artwork, $perPage);
    }

    /**
     * Add comment to artwork
     */
    public function addComment(int $id, User $user, string $content, ?int $parentId = null): ?object
    {
        $artwork = $this->artworkRepository->findById($id);

        if (!$artwork) {
            return null;
        }

        $parentComment = $parentId ? \App\Models\Comment::find($parentId) : null;

        return app(CommentService::class)->createComment($user, $artwork, $content, $parentComment);
    }

    /**
     * Get user's artworks
     */
    public function getUserArtworks(User $user, int $perPage = 15): object
    {
        return $this->artworkRepository->getByUser($user, $perPage);
    }
}
