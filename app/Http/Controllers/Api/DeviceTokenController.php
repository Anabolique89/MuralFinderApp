<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceTokenController extends ApiBaseController
{
    /**
     * Register a device token for push notifications
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'platform' => 'required|string|in:fcm,apns,web',
            'device_id' => 'nullable|string',
            'device_name' => 'nullable|string',
            'app_version' => 'nullable|string',
            'os_version' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            
            $deviceToken = DeviceToken::registerToken(
                $user,
                $request->token,
                $request->platform,
                $request->only(['device_id', 'device_name', 'app_version', 'os_version'])
            );

            return $this->sendSuccess($deviceToken, 'Device token registered successfully');
        } catch (\Exception $e) {
            logger()->error('Device token registration error: ' . $e->getMessage());
            return $this->sendError('Failed to register device token', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update device token
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_name' => 'nullable|string',
            'app_version' => 'nullable|string',
            'os_version' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $deviceToken = DeviceToken::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $deviceToken->update($request->only(['device_name', 'app_version', 'os_version', 'is_active']));

            return $this->sendSuccess($deviceToken, 'Device token updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError('Device token not found', JsonResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            logger()->error('Device token update error: ' . $e->getMessage());
            return $this->sendError('Failed to update device token', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Deactivate device token
     */
    public function deactivate(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $deviceToken = DeviceToken::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $deviceToken->deactivate();

            return $this->sendSuccess(null, 'Device token deactivated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError('Device token not found', JsonResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            logger()->error('Device token deactivation error: ' . $e->getMessage());
            return $this->sendError('Failed to deactivate device token', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user's device tokens
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $deviceTokens = $user->deviceTokens()
                ->orderBy('last_used_at', 'desc')
                ->get();

            return $this->sendSuccess($deviceTokens, 'Device tokens retrieved successfully');
        } catch (\Exception $e) {
            logger()->error('Device tokens retrieval error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve device tokens', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete device token
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $deviceToken = DeviceToken::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $deviceToken->delete();

            return $this->sendSuccess(null, 'Device token deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError('Device token not found', JsonResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            logger()->error('Device token deletion error: ' . $e->getMessage());
            return $this->sendError('Failed to delete device token', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
