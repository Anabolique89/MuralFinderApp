<?php

namespace App\Repositories;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentRepository extends BaseRepository
{
    public function __construct(Comment $model)
    {
        parent::__construct($model);
    }

    /**
     * Get comments for a commentable model
     */
    public function getForModel(Model $model, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('commentable_type', get_class($model))
            ->where('commentable_id', $model->id)
            ->published()
            ->topLevel()
            ->with(['user.profile', 'replies.user.profile'])
            ->orderBy('created_at')
            ->paginate($perPage);
    }

    /**
     * Get replies for a comment
     */
    public function getReplies(Comment $comment): Collection
    {
        return $this->model
            ->where('parent_id', $comment->id)
            ->published()
            ->with(['user.profile'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get comments by user
     */
    public function getByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $user->id)
            ->with(['commentable', 'user.profile'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get recent comments
     */
    public function getRecent(int $limit = 10): Collection
    {
        return $this->model
            ->published()
            ->with(['user.profile', 'commentable'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get most liked comments
     */
    public function getMostLiked(int $limit = 10): Collection
    {
        return $this->model
            ->published()
            ->orderByDesc('likes_count')
            ->with(['user.profile', 'commentable'])
            ->limit($limit)
            ->get();
    }

    /**
     * Search comments
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->published()
            ->where('content', 'LIKE', "%{$query}%")
            ->with(['user.profile', 'commentable'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get comments needing moderation
     */
    public function getNeedingModeration(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('status', Comment::STATUS_UNDER_REVIEW)
            ->with(['user.profile', 'commentable'])
            ->orderBy('created_at')
            ->paginate($perPage);
    }

    /**
     * Get comment thread (comment and all its replies)
     */
    public function getThread(Comment $comment): Collection
    {
        $thread = collect([$comment]);
        
        $replies = $this->model
            ->where('parent_id', $comment->id)
            ->published()
            ->with(['user.profile'])
            ->orderBy('created_at')
            ->get();

        return $thread->merge($replies);
    }

    /**
     * Count comments for a model
     */
    public function countForModel(Model $model): int
    {
        return $this->model
            ->where('commentable_type', get_class($model))
            ->where('commentable_id', $model->id)
            ->published()
            ->count();
    }
}
