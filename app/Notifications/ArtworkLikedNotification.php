<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Artwork;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ArtworkLikedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $liker;
    protected Artwork $artwork;

    public function __construct(User $liker, Artwork $artwork)
    {
        $this->liker = $liker;
        $this->artwork = $artwork;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->notificationPreferences?->emailEnabled('artwork_liked')) {
            $channels[] = 'mail';
        }

        if ($notifiable->notificationPreferences?->pushEnabled('artwork_liked')) {
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
            ->subject('Your Artwork Got a Like! - MuralFinder')
            ->greeting('Hello ' . $notifiable->profile?->first_name . '!')
            ->line($this->liker->profile?->first_name . ' liked your artwork "' . $this->artwork->title . '".')
            ->action('View Artwork', url('/artworks/' . $this->artwork->id))
            ->line('Keep creating amazing art!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'artwork_liked',
            'liker_id' => $this->liker->id,
            'liker_username' => $this->liker->username,
            'liker_name' => $this->liker->profile?->first_name . ' ' . $this->liker->profile?->last_name,
            'liker_avatar' => $this->liker->profile?->profile_image_url,
            'artwork_id' => $this->artwork->id,
            'artwork_title' => $this->artwork->title,
            'artwork_image' => $this->artwork->image_path,
            'message' => $this->liker->profile?->first_name . ' liked your artwork "' . $this->artwork->title . '"',
            'action_url' => '/artworks/' . $this->artwork->id,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'artwork_liked',
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
            'title' => 'Artwork Liked!',
            'body' => $this->liker->profile?->first_name . ' liked your artwork "' . $this->artwork->title . '"',
            'icon' => $this->artwork->image_path,
            'click_action' => url('/artworks/' . $this->artwork->id),
            'data' => [
                'type' => 'artwork_liked',
                'artwork_id' => $this->artwork->id,
                'liker_id' => $this->liker->id,
                'action_url' => '/artworks/' . $this->artwork->id,
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
