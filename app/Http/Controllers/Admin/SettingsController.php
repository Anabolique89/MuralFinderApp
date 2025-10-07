<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * Get all AI generation limit settings
     *
     * @return JsonResponse
     */
    public function getAIGenerationLimits(): JsonResponse
    {
        $limits = [
            'free' => Setting::get('ai_generation_limit_free', 10),
            'basic' => Setting::get('ai_generation_limit_basic', 50),
            'premium' => Setting::get('ai_generation_limit_premium', 200),
            'pro' => Setting::get('ai_generation_limit_pro', 1000),
        ];

        return response()->json([
            'success' => true,
            'data' => $limits
        ]);
    }

    /**
     * Update AI generation limit settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAIGenerationLimits(Request $request): JsonResponse
    {
        $request->validate([
            'free' => 'required|integer|min:1|max:1000',
            'basic' => 'required|integer|min:1|max:10000',
            'premium' => 'required|integer|min:1|max:50000',
            'pro' => 'required|integer|min:1|max:100000',
        ]);

        try {
            Setting::set('ai_generation_limit_free', $request->free, 'integer');
            Setting::set('ai_generation_limit_basic', $request->basic, 'integer');
            Setting::set('ai_generation_limit_premium', $request->premium, 'integer');
            Setting::set('ai_generation_limit_pro', $request->pro, 'integer');

            return response()->json([
                'success' => true,
                'message' => 'AI generation limits updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all settings
     *
     * @return JsonResponse
     */
    public function getAllSettings(): JsonResponse
    {
        $settings = Setting::all()->mapWithKeys(function ($setting) {
            return [$setting->key => [
                'value' => $setting->value,
                'type' => $setting->type,
                'description' => $setting->description
            ]];
        });

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Update a specific setting
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSetting(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
            'type' => 'required|in:string,integer,boolean,json'
        ]);

        try {
            Setting::set($request->key, $request->value, $request->type);

            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting: ' . $e->getMessage()
            ], 500);
        }
    }
}
