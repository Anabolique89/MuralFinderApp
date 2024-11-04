<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Notifications\ActivityNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Send a notification to a user for a specific entity.
     */
    public function testNotification($userId, $entityType, $entityId)
    {
        // Retrieve the user to be notified
        $user = User::find($userId);

        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        // Define supported entity types and corresponding model classes
        $supportedEntities = [
            'post' => \App\Models\Post::class,
            'artwork' => \App\Models\Artwork::class,
        ];

        // Verify if the entityType is supported and retrieve the correct model
        if (!array_key_exists($entityType, $supportedEntities)) {
            return response()->json(['error' => 'Unsupported entity type.'], 400);
        }

        $entityClass = $supportedEntities[$entityType];
        $entity = $entityClass::find($entityId);

        // Check if the entity exists
        if (!$entity) {
            return response()->json(['error' => ucfirst($entityType) . ' not found.'], 404);
        }

        // Define the activity type
        $activityType = ActivityType::POST_LIKED; // Modify this as needed

        // Send the notification to the user
        try {
            $user->notify(new ActivityNotification($activityType, $user, $entity));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => 'Notification sent successfully.']);
    }

    /**
     * Fetch notifications for the authenticated user.
     */
    public function getNotifications(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(10); // Modify as needed
        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($notificationId, Request $request)
    {
        $notification = $request->user()->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json(['error' => 'Notification not found.'], 404);
        }

        $notification->markAsRead();

        return response()->json(['success' => 'Notification marked as read.']);
    }
}
