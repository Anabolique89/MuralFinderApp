<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $commenter;
    protected Comment $comment;
    protected string $contentType;

    public function __construct(User $commenter, Comment $comment)
    {
        $this->commenter = $commenter;
        $this->comment = $comment;
        $this->contentType = $this->getContentType();
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['database'];

        $preferenceKey = $this->contentType . '_commented';
        if ($notifiable->notificationPreferences?->emailEnabled($preferenceKey)) {
            $channels[] = 'mail';
        }

        if ($notifiable->notificationPreferences?->pushEnabled($preferenceKey)) {
            $channels[] = 'fcm';
        }

        $channels[] = 'broadcast';

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $contentTitle = $this->getContentTitle();
        
        return (new MailMessage)
            ->subject('New Comment on Your ' . ucfirst($this->contentType) . ' - MuralFinder')
            ->greeting('Hello ' . $notifiable->profile?->first_name . '!')
            ->line($this->commenter->profile?->first_name . ' commented on your ' . $this->contentType . ' "' . $contentTitle . '".')
            ->line('Comment: "' . substr($this->comment->content, 0, 100) . (strlen($this->comment->content) > 100 ? '...' : '') . '"')
            ->action('View Comment', $this->getActionUrl())
            ->line('Thanks for engaging with the MuralFinder community!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => $this->contentType . '_commented',
            'commenter_id' => $this->commenter->id,
            'commenter_username' => $this->commenter->username,
            'commenter_name' => $this->commenter->profile?->first_name . ' ' . $this->commenter->profile?->last_name,
            'commenter_avatar' => $this->commenter->profile?->profile_image_url,
            'comment_id' => $this->comment->id,
            'comment_content' => substr($this->comment->content, 0, 100),
            'content_type' => $this->contentType,
            'content_id' => $this->comment->commentable_id,
            'content_title' => $this->getContentTitle(),
            'message' => $this->commenter->profile?->first_name . ' commented on your ' . $this->contentType,
            'action_url' => $this->getActionUrl(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => $this->contentType . '_commented',
            'data' => $this->toArray($notifiable),
            'created_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable): array
    {
        return [
            'title' => 'New Comment',
            'body' => $this->commenter->profile?->first_name . ' commented on your ' . $this->contentType,
            'icon' => $this->commenter->profile?->profile_image_url,
            'click_action' => $this->getActionUrl(),
            'data' => [
                'type' => $this->contentType . '_commented',
                'comment_id' => $this->comment->id,
                'content_id' => $this->comment->commentable_id,
                'commenter_id' => $this->commenter->id,
                'action_url' => $this->getActionUrl(),
            ],
        ];
    }

    /**
     * Get content type from commentable model
     */
    private function getContentType(): string
    {
        return match($this->comment->commentable_type) {
            'App\Models\Artwork' => 'artwork',
            'App\Models\Post' => 'post',
            'App\Models\Wall' => 'wall',
            default => 'content',
        };
    }

    /**
     * Get content title
     */
    private function getContentTitle(): string
    {
        $commentable = $this->comment->commentable;
        
        return match($this->comment->commentable_type) {
            'App\Models\Artwork' => $commentable->title ?? 'Untitled Artwork',
            'App\Models\Post' => $commentable->title ?? 'Untitled Post',
            'App\Models\Wall' => $commentable->name ?? 'Wall at ' . $commentable->location_text,
            default => 'Content',
        };
    }

    /**
     * Get action URL
     */
    private function getActionUrl(): string
    {
        return match($this->comment->commentable_type) {
            'App\Models\Artwork' => url('/artworks/' . $this->comment->commentable_id),
            'App\Models\Post' => url('/posts/' . $this->comment->commentable_id),
            'App\Models\Wall' => url('/walls/' . $this->comment->commentable_id),
            default => url('/'),
        };
    }

    /**
     * Determine which queues should be used for each notification channel.
     */
    public function viaQueues(): array
    {
        return [
            'mail' => 'emails',
            'fcm' => 'push-notifications',
            'database' => 'default',
        ];
    }
}
