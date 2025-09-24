<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class FollowNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $follower;

    public function __construct(User $follower)
    {
        $this->follower = $follower;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['database'];

        // Check user preferences
        if ($notifiable->notificationPreferences?->emailEnabled('new_follower')) {
            $channels[] = 'mail';
        }

        if ($notifiable->notificationPreferences?->pushEnabled('new_follower')) {
            $channels[] = 'fcm'; // Firebase Cloud Messaging
        }

        // Always include broadcast for real-time updates
        $channels[] = 'broadcast';

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Follower - MuralFinder')
            ->greeting('Hello ' . $notifiable->profile?->first_name . '!')
            ->line($this->follower->profile?->first_name . ' ' . $this->follower->profile?->last_name . ' (@' . $this->follower->username . ') started following you on MuralFinder.')
            ->action('View Profile', url('/users/' . $this->follower->username))
            ->line('Thank you for being part of the MuralFinder community!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'follow',
            'follower_id' => $this->follower->id,
            'follower_username' => $this->follower->username,
            'follower_name' => $this->follower->profile?->first_name . ' ' . $this->follower->profile?->last_name,
            'follower_avatar' => $this->follower->profile?->profile_image_url,
            'message' => $this->follower->profile?->first_name . ' started following you',
            'action_url' => '/users/' . $this->follower->username,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'follow',
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
            'title' => 'New Follower',
            'body' => $this->follower->profile?->first_name . ' started following you',
            'icon' => $this->follower->profile?->profile_image_url,
            'click_action' => url('/users/' . $this->follower->username),
            'data' => [
                'type' => 'follow',
                'follower_id' => $this->follower->id,
                'action_url' => '/users/' . $this->follower->username,
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
