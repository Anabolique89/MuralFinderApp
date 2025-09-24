<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\PostView;
use App\Repositories\PostRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PostService
{
    protected PostRepository $postRepository;
    protected ImageService $imageService;
    protected NotificationService $notificationService;

    public function __construct(
        PostRepository $postRepository,
        ImageService $imageService,
        NotificationService $notificationService
    ) {
        $this->postRepository = $postRepository;
        $this->imageService = $imageService;
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new post
     */
    public function createPost(User $user, array $postData, array $images = [], ?UploadedFile $featuredImage = null): Post
    {
        return DB::transaction(function () use ($user, $postData, $images, $featuredImage) {
            // Generate slug
            $slug = $this->generateUniqueSlug($postData['title']);

            // Upload featured image
            $featuredImagePath = null;
            if ($featuredImage) {
                $featuredImagePath = $this->imageService->uploadPostImage($featuredImage, $user->id);
            }

            // Upload additional images
            $uploadedImages = [];
            if (!empty($images)) {
                foreach ($images as $image) {
                    $imagePath = $this->imageService->uploadPostImage($image, $user->id);
                    $uploadedImages[] = $imagePath;
                }
            }

            // Create post
            $post = $this->postRepository->create([
                'user_id' => $user->id,
                'title' => $postData['title'],
                'content' => $postData['content'],
                'excerpt' => $postData['excerpt'] ?? null,
                'category_id' => $postData['category_id'] ?? null,
                'type' => $postData['type'],
                'featured_image_path' => $featuredImagePath,
                'images' => $uploadedImages,
                'tags' => $postData['tags'] ?? [],
                'is_published' => $postData['is_published'] ?? true,
                'publish_at' => $postData['publish_at'] ?? now(),
                'meta_title' => $postData['meta_title'] ?? null,
                'meta_description' => $postData['meta_description'] ?? null,
                'allow_comments' => $postData['allow_comments'] ?? true,
                'is_featured' => $postData['is_featured'] ?? false,
                'status' => Post::STATUS_PUBLISHED,
                'slug' => $slug,
            ]);

            // Update user's post count
            $user->profile?->incrementPostsCount();

            // Update category count
            if ($post->category) {
                $post->category->incrementPostsCount();
            }

            return $post->load(['user.profile', 'category']);
        });
    }

    /**
     * Update post
     */
    public function updatePost(Post $post, array $postData): Post
    {
        // Update slug if title changed
        if (isset($postData['title']) && $postData['title'] !== $post->title) {
            $postData['slug'] = $this->generateUniqueSlug($postData['title'], $post->id);
        }

        $this->postRepository->update($post, $postData);

        return $post->fresh(['user.profile', 'category']);
    }

    /**
     * Delete post
     */
    public function deletePost(Post $post): bool
    {
        return DB::transaction(function () use ($post) {
            // Update counts
            $post->user->profile?->decrementPostsCount();
            $post->category?->decrementPostsCount();

            // Delete images
            $this->imageService->deletePostImages($post);

            return $this->postRepository->delete($post);
        });
    }

    /**
     * Like post
     */
    public function likePost(Post $post, User $user): bool
    {
        if ($post->likes()->where('user_id', $user->id)->exists()) {
            return false;
        }

        $post->likes()->create([
            'user_id' => $user->id,
            'reaction_type' => 'like',
        ]);

        $post->increment('likes_count');

        // Create notification
        if ($post->user_id !== $user->id) {
            $this->notificationService->createPostLikedNotification($post, $user);
        }

        return true;
    }

    /**
     * Unlike post
     */
    public function unlikePost(Post $post, User $user): bool
    {
        $like = $post->likes()->where('user_id', $user->id)->first();

        if (!$like) {
            return false;
        }

        $like->delete();
        $post->decrement('likes_count');

        return true;
    }

    /**
     * Record post view
     */
    public function recordView(Post $post, User $user = null, array $context = []): void
    {
        PostView::recordView($post, $user, $context);

        // Update view count if it's a unique view
        if (!$user || !PostView::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->where('is_unique_view', true)
            ->exists()) {
            $post->increment('views_count');
        }
    }

    /**
     * Get posts feed
     */
    public function getPostsFeed(array $filters = [], int $perPage = 15)
    {
        if (!empty($filters)) {
            return $this->postRepository->search('', $filters, $perPage);
        }

        return $this->postRepository->getPublished($perPage);
    }

    /**
     * Get featured posts
     */
    public function getFeaturedPosts(int $limit = 10)
    {
        return $this->postRepository->getFeatured($limit);
    }

    /**
     * Get trending posts
     */
    public function getTrendingPosts(int $limit = 10)
    {
        return $this->postRepository->getTrending($limit);
    }

    /**
     * Search posts
     */
    public function searchPosts(string $query, array $filters = [], int $perPage = 15)
    {
        return $this->postRepository->search($query, $filters, $perPage);
    }

    /**
     * Get posts by user
     */
    public function getUserPosts(User $user, int $perPage = 15)
    {
        return $this->postRepository->getByUser($user, $perPage);
    }

    /**
     * Feature post
     */
    public function featurePost(Post $post): bool
    {
        $post->feature();
        return true;
    }

    /**
     * Unfeature post
     */
    public function unfeaturePost(Post $post): bool
    {
        $post->unfeature();
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
            $query = Post::where('slug', $slug);

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
     * Get post by ID with view recording
     */
    public function getPostById(int $id, ?User $viewer = null): ?Post
    {
        $post = $this->postRepository->findById($id);

        if ($post && $viewer) {
            $this->recordView($post, $viewer, [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return $post;
    }

    /**
     * Update post by ID with ownership check
     */
    public function updatePostById(int $id, User $user, array $data): ?Post
    {
        $post = $this->postRepository->findById($id);

        if (!$post || $post->user_id !== $user->id) {
            return null;
        }

        return $this->updatePost($post, $data);
    }

    /**
     * Delete post by ID with ownership check
     */
    public function deletePostById(int $id, User $user): bool
    {
        $post = $this->postRepository->findById($id);

        if (!$post || $post->user_id !== $user->id) {
            return false;
        }

        $this->deletePost($post);
        return true;
    }

    /**
     * Toggle like for post by ID
     */
    public function toggleLike(int $id, User $user): ?array
    {
        $post = $this->postRepository->findById($id);

        if (!$post) {
            return null;
        }

        $isLiked = $post->likes()->where('user_id', $user->id)->exists();

        if ($isLiked) {
            $this->unlikePost($post, $user);
            $message = 'Post unliked successfully';
        } else {
            $this->likePost($post, $user);
            $message = 'Post liked successfully';
        }

        return [
            'liked' => !$isLiked,
            'likes_count' => $post->fresh()->likes_count,
            'message' => $message,
        ];
    }

    /**
     * Get post comments
     */
    public function getPostComments(int $id, int $perPage = 20): ?object
    {
        $post = $this->postRepository->findById($id);

        if (!$post) {
            return null;
        }

        return app(CommentService::class)->getCommentsForModel($post, $perPage);
    }

    /**
     * Add comment to post
     */
    public function addComment(int $id, User $user, string $content, ?int $parentId = null): ?object
    {
        $post = $this->postRepository->findById($id);

        if (!$post) {
            return null;
        }

        $parentComment = $parentId ? \App\Models\Comment::find($parentId) : null;

        return app(CommentService::class)->createComment($user, $post, $content, $parentComment);
    }
}
