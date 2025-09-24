<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PostLikedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $liker;
    protected Post $post;

    public function __construct(User $liker, Post $post)
    {
        $this->liker = $liker;
        $this->post = $post;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->notificationPreferences?->emailEnabled('post_liked')) {
            $channels[] = 'mail';
        }

        if ($notifiable->notificationPreferences?->pushEnabled('post_liked')) {
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
        return (new MailMessage)
            ->subject('Your Post Got a Like! - MuralFinder')
            ->greeting('Hello ' . $notifiable->profile?->first_name . '!')
            ->line($this->liker->profile?->first_name . ' liked your post "' . $this->post->title . '".')
            ->action('View Post', url('/posts/' . $this->post->id))
            ->line('Keep sharing great content!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'post_liked',
            'liker_id' => $this->liker->id,
            'liker_username' => $this->liker->username,
            'liker_name' => $this->liker->profile?->first_name . ' ' . $this->liker->profile?->last_name,
            'liker_avatar' => $this->liker->profile?->profile_image_url,
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_image' => $this->post->featured_image_path,
            'message' => $this->liker->profile?->first_name . ' liked your post "' . $this->post->title . '"',
            'action_url' => '/posts/' . $this->post->id,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'post_liked',
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
            'title' => 'Post Liked!',
            'body' => $this->liker->profile?->first_name . ' liked your post "' . $this->post->title . '"',
            'icon' => $this->post->featured_image_path,
            'click_action' => url('/posts/' . $this->post->id),
            'data' => [
                'type' => 'post_liked',
                'post_id' => $this->post->id,
                'liker_id' => $this->liker->id,
                'action_url' => '/posts/' . $this->post->id,
            ],
        ];
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
