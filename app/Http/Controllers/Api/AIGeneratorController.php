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
                'output' => $result,
                'service' => 'Minimax Image-01',
                'archetype' => $archetype
            ], 'Image generated successfully');

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
                'output' => $result,
                'service' => 'Minimax Image-01',
                'archetype' => $archetype,
                'note' => $imageFile
                    ? 'Generated themed image using your photo as character reference!'
                    : 'Generated themed image based on your archetype selection. Upload a photo for personalized results!'
            ], 'Image generated successfully');

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
                'archetype' => 'required|string|in:viking,royal,norse',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation failed: ' . implode(', ', $validator->errors()->all()), 422);
            }

            $imageUrl = $request->input('image_url');
            $archetype = $request->input('archetype');
            $title = $request->input('title') ?: "AI Generated " . ucfirst($archetype) . " Artwork";
            $description = $request->input('description') ?: "This artwork was generated using AI with the {$archetype} archetype. Created using MuralFinder's AI Generator.";

            Log::info("Uploading AI generated image as artwork", [
                'user_id' => auth()->id(),
                'archetype' => $archetype,
                'image_url' => $imageUrl
            ]);

            $result = $this->aiGeneratorService->uploadGeneratedImageAsArtwork(
                $imageUrl,
                $archetype,
                $title,
                $description
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
