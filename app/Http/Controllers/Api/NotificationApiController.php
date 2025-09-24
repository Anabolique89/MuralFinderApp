<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\ApiBaseController;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationApiController extends ApiBaseController
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user notifications
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 20);

            $notifications = $this->notificationService->getUserNotifications($user, $perPage);

            return $this->sendSuccess($notifications, 'Notifications retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Notifications index error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve notifications', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $count = $this->notificationService->getUnreadCount($user);

            return $this->sendSuccess(['count' => $count], 'Unread count retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Unread count error: ' . $e->getMessage());
            return $this->sendError('Failed to get unread count', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $notification = $user->notifications()->findOrFail($id);

            $this->notificationService->markAsRead($notification);

            return $this->sendSuccess(null, 'Notification marked as read');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError('Notification not found', JsonResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            logger()->error('Mark as read error: ' . $e->getMessage());
            return $this->sendError('Failed to mark notification as read', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $this->notificationService->markAllAsRead($user);

            return $this->sendSuccess(null, 'All notifications marked as read');
        } catch (\Exception $e) {
            logger()->error('Mark all as read error: ' . $e->getMessage());
            return $this->sendError('Failed to mark all notifications as read', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $notification = $user->notifications()->findOrFail($id);

            $this->notificationService->deleteNotification($notification);

            return $this->sendSuccess(null, 'Notification deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError('Notification not found', JsonResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            logger()->error('Notification deletion error: ' . $e->getMessage());
            return $this->sendError('Failed to delete notification', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get notification preferences
     */
    public function preferences(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $preferences = $user->notificationPreferences;

            if (!$preferences) {
                // Create default preferences if they don't exist
                $preferences = $user->notificationPreferences()->create(
                    \App\Models\NotificationPreference::getDefaults()
                );
            }

            return $this->sendSuccess($preferences, 'Notification preferences retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Notification preferences error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve notification preferences', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email_new_follower' => 'boolean',
            'email_artwork_liked' => 'boolean',
            'email_artwork_commented' => 'boolean',
            'email_post_liked' => 'boolean',
            'email_post_commented' => 'boolean',
            'email_wall_added_nearby' => 'boolean',
            'email_mentioned' => 'boolean',
            'email_weekly_digest' => 'boolean',
            'email_marketing' => 'boolean',
            'push_new_follower' => 'boolean',
            'push_artwork_liked' => 'boolean',
            'push_artwork_commented' => 'boolean',
            'push_post_liked' => 'boolean',
            'push_post_commented' => 'boolean',
            'push_wall_added_nearby' => 'boolean',
            'push_mentioned' => 'boolean',
            'push_live_events' => 'boolean',
            'app_new_follower' => 'boolean',
            'app_artwork_liked' => 'boolean',
            'app_artwork_commented' => 'boolean',
            'app_post_liked' => 'boolean',
            'app_post_commented' => 'boolean',
            'app_wall_added_nearby' => 'boolean',
            'app_mentioned' => 'boolean',
            'email_frequency' => 'in:immediate,hourly,daily,weekly,never',
            'push_frequency' => 'in:immediate,hourly,daily,weekly,never',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
            'timezone' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $preferencesData = $request->only([
                'email_new_follower', 'email_artwork_liked', 'email_artwork_commented',
                'email_post_liked', 'email_post_commented', 'email_wall_added_nearby',
                'email_mentioned', 'email_weekly_digest', 'email_marketing',
                'push_new_follower', 'push_artwork_liked', 'push_artwork_commented',
                'push_post_liked', 'push_post_commented', 'push_wall_added_nearby',
                'push_mentioned', 'push_live_events', 'app_new_follower',
                'app_artwork_liked', 'app_artwork_commented', 'app_post_liked',
                'app_post_commented', 'app_wall_added_nearby', 'app_mentioned',
                'email_frequency', 'push_frequency', 'quiet_hours_start',
                'quiet_hours_end', 'timezone'
            ]);

            $preferences = $user->notificationPreferences()->updateOrCreate(
                ['user_id' => $user->id],
                $preferencesData
            );

            return $this->sendSuccess($preferences, 'Notification preferences updated successfully');
        } catch (\Exception $e) {
            logger()->error('Notification preferences update error: ' . $e->getMessage());
            return $this->sendError('Failed to update notification preferences', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
