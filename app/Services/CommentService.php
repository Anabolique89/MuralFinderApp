<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\User;
use App\Models\Artwork;
use App\Models\Post;
use App\Models\Wall;
use App\Repositories\CommentRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CommentService
{
    protected CommentRepository $commentRepository;
    protected NotificationService $notificationService;

    public function __construct(
        CommentRepository $commentRepository,
        NotificationService $notificationService
    ) {
        $this->commentRepository = $commentRepository;
        $this->notificationService = $notificationService;
    }

    /**
     * Create a comment
     */
    public function createComment(
        User $user,
        Model $commentable,
        string $content,
        Comment $parent = null
    ): Comment {
        return DB::transaction(function () use ($user, $commentable, $content, $parent) {
            // Extract mentions from content
            $mentions = $this->extractMentions($content);

            // Create comment
            $comment = $this->commentRepository->create([
                'user_id' => $user->id,
                'commentable_id' => $commentable->id,
                'commentable_type' => get_class($commentable),
                'parent_id' => $parent?->id,
                'content' => $content,
                'mentions' => $mentions,
                'status' => Comment::STATUS_PUBLISHED,
            ]);

            // Update counts
            $commentable->increment('comments_count');
            
            if ($parent) {
                $parent->increment('replies_count');
            }

            // Create notifications
            $this->createCommentNotifications($comment, $commentable, $mentions);

            return $comment->load(['user.profile']);
        });
    }

    /**
     * Update a comment
     */
    public function updateComment(Comment $comment, string $content): Comment
    {
        $mentions = $this->extractMentions($content);

        $this->commentRepository->update($comment, [
            'content' => $content,
            'mentions' => $mentions,
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        return $comment->fresh(['user.profile']);
    }

    /**
     * Delete a comment
     */
    public function deleteComment(Comment $comment): bool
    {
        return DB::transaction(function () use ($comment) {
            // Update counts
            $comment->commentable->decrement('comments_count');
            
            if ($comment->parent) {
                $comment->parent->decrement('replies_count');
            }

            return $this->commentRepository->delete($comment);
        });
    }

    /**
     * Like a comment
     */
    public function likeComment(Comment $comment, User $user): bool
    {
        if ($comment->likes()->where('user_id', $user->id)->exists()) {
            return false;
        }

        $comment->likes()->create([
            'user_id' => $user->id,
            'reaction_type' => 'like',
        ]);

        $comment->increment('likes_count');

        return true;
    }

    /**
     * Unlike a comment
     */
    public function unlikeComment(Comment $comment, User $user): bool
    {
        $like = $comment->likes()->where('user_id', $user->id)->first();
        
        if (!$like) {
            return false;
        }

        $like->delete();
        $comment->decrement('likes_count');

        return true;
    }

    /**
     * Get comments for a model
     */
    public function getCommentsForModel(Model $model, int $perPage = 20)
    {
        return $this->commentRepository->getForModel($model, $perPage);
    }

    /**
     * Get replies for a comment
     */
    public function getReplies(Comment $comment)
    {
        return $this->commentRepository->getReplies($comment);
    }

    /**
     * Hide a comment (moderation)
     */
    public function hideComment(Comment $comment): bool
    {
        $comment->hide();
        return true;
    }

    /**
     * Publish a comment (moderation)
     */
    public function publishComment(Comment $comment): bool
    {
        $comment->publish();
        return true;
    }

    /**
     * Extract mentions from comment content
     */
    protected function extractMentions(string $content): array
    {
        preg_match_all('/@(\w+)/', $content, $matches);
        
        if (empty($matches[1])) {
            return [];
        }

        // Get user IDs for mentioned usernames
        $usernames = $matches[1];
        $users = User::whereIn('username', $usernames)->get(['id', 'username']);
        
        return $users->map(function ($user) {
            return [
                'user_id' => $user->id,
                'username' => $user->username,
            ];
        })->toArray();
    }

    /**
     * Create notifications for comment
     */
    protected function createCommentNotifications(Comment $comment, Model $commentable, array $mentions): void
    {
        // Notify the content owner
        if ($commentable->user_id !== $comment->user_id) {
            switch (get_class($commentable)) {
                case Artwork::class:
                    $this->notificationService->createArtworkCommentedNotification($commentable, $comment);
                    break;
                case Post::class:
                    $this->notificationService->createPostCommentedNotification($commentable, $comment);
                    break;
                case Wall::class:
                    $this->notificationService->createWallCommentedNotification($commentable, $comment);
                    break;
            }
        }

        // Notify mentioned users
        foreach ($mentions as $mention) {
            $mentionedUser = User::find($mention['user_id']);
            if ($mentionedUser && $mentionedUser->id !== $comment->user_id) {
                $this->notificationService->createMentionNotification(
                    $mentionedUser,
                    $comment,
                    $commentable
                );
            }
        }
    }

    /**
     * Get comment statistics
     */
    public function getCommentStats(Comment $comment): array
    {
        return [
            'likes_count' => $comment->likes_count,
            'replies_count' => $comment->replies_count,
            'is_edited' => $comment->is_edited,
            'edited_at' => $comment->edited_at,
            'mentions_count' => count($comment->mentions ?? []),
        ];
    }
}
