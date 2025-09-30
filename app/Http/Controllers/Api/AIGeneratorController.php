<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Base\ApiBaseController;
use App\Services\AIGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AIGeneratorController extends ApiBaseController
{
    protected $aiGeneratorService;

    public function __construct(AIGeneratorService $aiGeneratorService)
    {
        $this->aiGeneratorService = $aiGeneratorService;
    }

    /**
     * Generate themed image using archetype without reference image
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateArchetype(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'archetype' => 'required|string|in:viking,royal,norse'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation failed: ' . implode(', ', $validator->errors()->all()), 422);
            }

            $archetype = $request->input('archetype');

            Log::info("Generating AI archetype: {$archetype}");

            $result = $this->aiGeneratorService->generateThemedImage($archetype);

            return $this->sendSuccess([
                'prediction_id' => $result['prediction_id'],
                'status' => $result['status'],
                'message' => $result['message'],
                'service' => 'Minimax Image-01',
                'archetype' => $archetype
            ], 'Image generation started successfully');

        } catch (\Exception $e) {
            Log::error('AI Generator Error: ' . $e->getMessage(), [
                'archetype' => $request->input('archetype'),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError(
                'Failed to generate image: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Generate themed image using archetype with reference image
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgeSaga(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'archetype' => 'required|string|in:viking,royal,norse',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240' // 10MB max
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation failed: ' . implode(', ', $validator->errors()->all()), 422);
            }

            $archetype = $request->input('archetype');
            $imageFile = $request->file('image');

            Log::info("Forging AI saga: {$archetype}" . ($imageFile ? ' with reference image' : ''));

            $result = $this->aiGeneratorService->generateThemedImage($archetype, $imageFile);

            return $this->sendSuccess([
                'prediction_id' => $result['prediction_id'],
                'status' => $result['status'],
                'message' => $result['message'],
                'service' => 'Minimax Image-01',
                'archetype' => $archetype,
                'note' => $imageFile
                    ? 'Generated themed image using your photo as character reference!'
                    : 'Generated themed image based on your archetype selection. Upload a photo for personalized results!'
            ], 'Image generation started successfully');

        } catch (\Exception $e) {
            Log::error('AI Generator Error: ' . $e->getMessage(), [
                'archetype' => $request->input('archetype'),
                'has_image' => $request->hasFile('image'),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError(
                'Failed to generate image: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Generate themed image using custom prompt without reference image
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateCustom(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'prompt' => 'required|string|min:10|max:500'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation failed: ' . implode(', ', $validator->errors()->all()), 422);
            }

            $prompt = $request->input('prompt');

            Log::info("Generating AI custom prompt: {$prompt}");

            $result = $this->aiGeneratorService->generateCustomImage($prompt);

            return $this->sendSuccess([
                'prediction_id' => $result['prediction_id'],
                'status' => $result['status'],
                'message' => $result['message'],
                'service' => 'Minimax Image-01',
                'prompt' => $prompt
            ], 'Custom image generation started successfully');

        } catch (\Exception $e) {
            Log::error('AI Custom Generator Error: ' . $e->getMessage(), [
                'prompt' => $request->input('prompt'),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError(
                'Failed to generate custom image: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Generate themed image using custom prompt with reference image
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgeCustomSaga(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'prompt' => 'required|string|min:10|max:500',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240' // 10MB max
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation failed: ' . implode(', ', $validator->errors()->all()), 422);
            }

            $prompt = $request->input('prompt');
            $imageFile = $request->file('image');

            Log::info("Forging AI custom saga: {$prompt}" . ($imageFile ? ' with reference image' : ''));

            $result = $this->aiGeneratorService->generateCustomImage($prompt, $imageFile);

            return $this->sendSuccess([
                'prediction_id' => $result['prediction_id'],
                'status' => $result['status'],
                'message' => $result['message'],
                'service' => 'Minimax Image-01',
                'prompt' => $prompt,
                'note' => $imageFile
                    ? 'Generated custom image using your photo as character reference!'
                    : 'Generated custom image based on your prompt. Upload a photo for personalized results!'
            ], 'Custom image generation started successfully');

        } catch (\Exception $e) {
            Log::error('AI Custom Generator Error: ' . $e->getMessage(), [
                'prompt' => $request->input('prompt'),
                'has_image' => $request->hasFile('image'),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError(
                'Failed to generate custom image: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get available archetypes
     *
     * @return JsonResponse
     */
    public function getArchetypes(): JsonResponse
    {
        $archetypes = $this->aiGeneratorService->getAvailableArchetypes();

        return $this->sendSuccess([
            'archetypes' => $archetypes
        ], 'Archetypes retrieved successfully');
    }

    /**
     * Check prediction status and progress
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkPredictionStatus(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'prediction_id' => 'required|string'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation failed: ' . implode(', ', $validator->errors()->all()), 422);
            }

            $predictionId = $request->input('prediction_id');
            $result = $this->aiGeneratorService->checkPredictionStatus($predictionId);

            return $this->sendSuccess($result, 'Prediction status retrieved successfully');

        } catch (\Exception $e) {
            Log::error('AI Prediction Status Error: ' . $e->getMessage(), [
                'prediction_id' => $request->input('prediction_id'),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError(
                'Failed to check prediction status: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Upload generated image as artwork
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAsArtwork(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'image_url' => 'required|url',
                'archetype' => 'nullable|string|in:viking,royal,norse,custom',
                'prompt' => 'nullable|string|max:500',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation failed: ' . implode(', ', $validator->errors()->all()), 422);
            }

            $imageUrl = $request->input('image_url');
            $archetype = $request->input('archetype');
            $prompt = $request->input('prompt');
            $title = $request->input('title');
            $description = $request->input('description');

            // Determine if this is a custom prompt or archetype
            if ($prompt) {
                $title = $title ?: "AI Generated Custom Artwork";
                $description = $description ?: "This artwork was generated using AI with a custom prompt. Created using MuralFinder's AI Generator.";
                $archetype = 'custom';
            } else {
                $title = $title ?: "AI Generated " . ucfirst($archetype) . " Artwork";
                $description = $description ?: "This artwork was generated using AI with the {$archetype} archetype. Created using MuralFinder's AI Generator.";
            }

            Log::info("Uploading AI generated image as artwork", [
                'user_id' => auth()->id(),
                'archetype' => $archetype,
                'prompt' => $prompt,
                'image_url' => $imageUrl
            ]);

            $result = $this->aiGeneratorService->uploadGeneratedImageAsArtwork(
                $imageUrl,
                $archetype,
                $title,
                $description,
                $prompt
            );

            return $this->sendSuccess($result, 'Artwork uploaded successfully');

        } catch (\Exception $e) {
            Log::error('AI Artwork Upload Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'archetype' => $request->input('archetype'),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError(
                'Failed to upload artwork: ' . $e->getMessage(),
                500
            );
        }
    }
}
