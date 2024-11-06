<?php

namespace App\Notifications;

use App\Enums\ActivityType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ActivityNotification extends Notification
{
    use Queueable;

    public ActivityType $activityType;
    public $user;
    public $entity;

    public function __construct(ActivityType $activityType, $user, $entity)
    {
        $this->activityType = $activityType;
        $this->user = $user;
        $this->entity = $entity;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'activity_type' => $this->activityType->value, // Using the Enum value here
            'message' => $this->generateMessage(),
            'entity_id' => $this->entity->id,
            'user_id' => $this->user->id,
        ];
    }

    private function generateMessage()
    {
        return match ($this->activityType) {
            ActivityType::POST_LIKED => "{$this->user->name} liked your post.",
            ActivityType::POST_COMMENTED => "{$this->user->name} commented on your post.",
            ActivityType::ARTWORK_LIKED => "{$this->user->name} liked your artwork.",
            ActivityType::ARTWORK_COMMENTED => "{$this->user->name} commented on your artwork.",
            default => "{$this->user->name} performed an action.",
        };
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'notification' => [
                'id' => $this->id,
                'data' => [
                    'activity_type' => $this->activityType->value,
                    'message' => $this->generateMessage(),
                    'entity_id' => $this->entity->id,
                    'user_id' => $this->user->id,
                ],
                'created_at' => now(),
            ],
        ]);
    }



    public function broadcastOn()
    {
        return ['user.' . $this->entity->user_id, 'notifications'];
    }


    public function broadcastAs()
    {
        return 'ActivityNotification';
    }

}
