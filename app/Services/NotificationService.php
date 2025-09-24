<?php

namespace App\Services;

use App\Models\User;
use App\Models\Artwork;
use App\Models\Post;
use App\Models\Wall;
use App\Models\Comment;
use App\Models\Notification;
use App\Notifications\FollowNotification;
use App\Notifications\ArtworkLikedNotification;
use App\Notifications\PostLikedNotification;
use App\Notifications\CommentNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotificationService
{
    /**
     * Create follow notification
     */
    public function createFollowNotification(User $user, User $follower): void
    {
        // Send Laravel notification (handles email, push, database, broadcast)
        $user->notify(new FollowNotification($follower));
    }

    /**
     * Create artwork liked notification
     */
    public function createArtworkLikedNotification(Artwork $artwork, User $liker): void
    {
        // Send Laravel notification
        $artwork->user->notify(new ArtworkLikedNotification($liker, $artwork));
    }

    /**
     * Create artwork commented notification
     */
    public function createArtworkCommentedNotification(Artwork $artwork, Comment $comment): void
    {
        // Send Laravel notification
        $artwork->user->notify(new CommentNotification($comment->user, $comment));
    }

    /**
     * Create post liked notification
     */
    public function createPostLikedNotification(Post $post, User $liker): void
    {
        // Send Laravel notification
        $post->user->notify(new PostLikedNotification($liker, $post));
    }

    /**
     * Create post commented notification
     */
    public function createPostCommentedNotification(Post $post, Comment $comment): void
    {
        if (!$post->user->notificationPreferences?->app_post_commented) {
            return;
        }

        Notification::createNotification(
            user: $post->user,
            type: Notification::TYPE_POST_COMMENTED,
            title: 'New Comment',
            message: "{$comment->user->display_name} commented on your post \"{$post->title}\"",
            notifiable: $post,
            actor: $comment->user
        );
    }

    /**
     * Create wall liked notification
     */
    public function createWallLikedNotification(Wall $wall, User $liker): void
    {
        if (!$wall->addedBy->notificationPreferences?->app_wall_added_nearby) {
            return;
        }

        Notification::createNotification(
            user: $wall->addedBy,
            type: Notification::TYPE_WALL_LIKED,
            title: 'Wall Liked',
            message: "{$liker->display_name} liked the wall you added",
            notifiable: $wall,
            actor: $liker
        );
    }

    /**
     * Create wall commented notification
     */
    public function createWallCommentedNotification(Wall $wall, Comment $comment): void
    {
        if (!$wall->addedBy->notificationPreferences?->app_wall_added_nearby) {
            return;
        }

        Notification::createNotification(
            user: $wall->addedBy,
            type: Notification::TYPE_WALL_COMMENTED,
            title: 'Wall Comment',
            message: "{$comment->user->display_name} commented on the wall you added",
            notifiable: $wall,
            actor: $comment->user
        );
    }

    /**
     * Create mention notification
     */
    public function createMentionNotification(User $mentionedUser, Comment $comment, $mentionedIn): void
    {
        if (!$mentionedUser->notificationPreferences?->app_mentioned) {
            return;
        }

        $contentType = class_basename($mentionedIn);
        $title = match($contentType) {
            'Artwork' => $mentionedIn->title,
            'Post' => $mentionedIn->title,
            'Wall' => $mentionedIn->name ?? 'a wall',
            default => 'content'
        };

        Notification::createNotification(
            user: $mentionedUser,
            type: Notification::TYPE_MENTIONED,
            title: 'You were mentioned',
            message: "{$comment->user->display_name} mentioned you in a comment on {$title}",
            notifiable: $mentionedIn,
            actor: $comment->user
        );
    }

    /**
     * Create nearby wall notification
     */
    public function createNearbyWallNotification(User $user, Wall $wall): void
    {
        if (!$user->notificationPreferences?->app_wall_added_nearby) {
            return;
        }

        Notification::createNotification(
            user: $user,
            type: Notification::TYPE_WALL_ADDED_NEARBY,
            title: 'New Wall Nearby',
            message: "A new wall was added near your location",
            notifiable: $wall,
            actor: $wall->addedBy
        );
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(User $user, int $perPage = 20)
    {
        return $user->notifications()
            ->with(['actor', 'notifiable'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(User $user): void
    {
        $user->notifications()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()->unread()->count();
    }

    /**
     * Delete notification
     */
    public function deleteNotification(Notification $notification): bool
    {
        return $notification->delete();
    }

    /**
     * Send push notification (placeholder for actual implementation)
     */
    protected function sendPushNotification(User $user, array $data): void
    {
        // This would integrate with a push notification service like FCM, Pusher, etc.
        // For now, just mark as sent
    }

    /**
     * Send email notification (placeholder for actual implementation)
     */
    protected function sendEmailNotification(User $user, array $data): void
    {
        // This would integrate with email service
        // For now, just mark as sent
    }
}
