<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        // Get FCM server key from config
        $serverKey = config('services.fcm.server_key');
        
        if (!$serverKey) {
            Log::warning('FCM server key not configured');
            return;
        }

        // Get user's FCM tokens
        $tokens = $this->getFcmTokens($notifiable);
        
        if (empty($tokens)) {
            Log::info('No FCM tokens found for user: ' . $notifiable->id);
            return;
        }

        // Get notification data
        $fcmData = $notification->toFcm($notifiable);

        // Send to each token
        foreach ($tokens as $token) {
            $this->sendToToken($token, $fcmData, $serverKey);
        }
    }

    /**
     * Get FCM tokens for the user
     */
    private function getFcmTokens($notifiable): array
    {
        // Get tokens from user's device tokens table
        return $notifiable->deviceTokens()
            ->where('platform', 'fcm')
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();
    }

    /**
     * Send notification to a specific token
     */
    private function sendToToken(string $token, array $data, string $serverKey): void
    {
        $payload = [
            'to' => $token,
            'notification' => [
                'title' => $data['title'],
                'body' => $data['body'],
                'icon' => $data['icon'] ?? null,
                'click_action' => $data['click_action'] ?? null,
                'sound' => 'default',
            ],
            'data' => $data['data'] ?? [],
            'priority' => 'high',
            'content_available' => true,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['failure']) && $result['failure'] > 0) {
                    // Handle failed tokens (invalid/expired)
                    $this->handleFailedToken($token, $result);
                }
                
                Log::info('FCM notification sent successfully', [
                    'token' => substr($token, 0, 20) . '...',
                    'success' => $result['success'] ?? 0,
                    'failure' => $result['failure'] ?? 0,
                ]);
            } else {
                Log::error('FCM notification failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('FCM notification exception', [
                'message' => $e->getMessage(),
                'token' => substr($token, 0, 20) . '...',
            ]);
        }
    }

    /**
     * Handle failed/invalid tokens
     */
    private function handleFailedToken(string $token, array $result): void
    {
        if (isset($result['results'][0]['error'])) {
            $error = $result['results'][0]['error'];
            
            // If token is invalid or not registered, deactivate it
            if (in_array($error, ['InvalidRegistration', 'NotRegistered'])) {
                \App\Models\DeviceToken::where('token', $token)
                    ->update(['is_active' => false]);
                
                Log::info('Deactivated invalid FCM token', ['token' => substr($token, 0, 20) . '...']);
            }
        }
    }
}
