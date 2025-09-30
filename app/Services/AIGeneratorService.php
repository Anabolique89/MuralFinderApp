<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Artwork;
use App\Models\Category;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;

class AIGeneratorService
{
    protected $replicateApiToken;
    protected $replicateApiUrl = 'https://api.replicate.com/v1';
    protected $imageManager;

    public function __construct()
    {
        $this->replicateApiToken = config('services.replicate.api_token');
        $this->imageManager = new ImageManager(new Driver());

        if (!$this->replicateApiToken) {
            throw new Exception('Replicate API token not configured');
        }
    }

    /**
     * Generate themed image using Minimax image-01
     *
     * @param string $archetype
     * @param \Illuminate\Http\UploadedFile|null $subjectImageFile
     * @param int $retries
     * @return string
     * @throws Exception
     */
    public function generateThemedImage(string $archetype, $subjectImageFile = null, int $retries = 3): array
    {
        try {
            $prompts = $this->getArchetypePrompts();
            $prompt = $prompts[$archetype] ?? $prompts['viking'];

            $input = [
                'prompt' => $prompt,
                'aspect_ratio' => '1:1',
                'number_of_images' => 1,
                'prompt_optimizer' => true,
            ];

            // If user uploaded an image, use it as subject reference
            if ($subjectImageFile) {
                $processedImageData = $this->processImageForReplicate($subjectImageFile);
                $input['subject_reference'] = $processedImageData;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->replicateApiToken,
                'Content-Type' => 'application/json',
            ])->post($this->replicateApiUrl . '/predictions', [
                'version' => 'minimax/image-01',
                'input' => $input
            ]);

            if (!$response->successful()) {
                throw new Exception('Replicate API request failed: ' . $response->body());
            }

            $prediction = $response->json();
            $predictionId = $prediction['id'];

            Log::info("Prediction created: {$predictionId}");

            // Return prediction ID for frontend to poll
            return [
                'prediction_id' => $predictionId,
                'status' => 'starting',
                'message' => 'AI generation started successfully'
            ];

        } catch (Exception $e) {
            if ($e->getCode() === 429 && $retries > 0) {
                // Wait 3 seconds and try again for rate limiting
                sleep(3);
                return $this->generateThemedImage($archetype, $subjectImageFile, $retries - 1);
            }

            Log::error('Error generating themed image: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process image for Replicate API
     *
     * @param \Illuminate\Http\UploadedFile $imageFile
     * @return string
     */
    protected function processImageForReplicate($imageFile): string
    {
        // Process the image to ensure it meets requirements
        $image = $this->imageManager->read($imageFile->getPathname())
            ->resize(1024, 1024)
            ->toJpeg(90);

        // Convert to base64 data URL
        $base64Image = base64_encode($image);
        return 'data:image/jpeg;base64,' . $base64Image;
    }

    /**
     * Poll for prediction completion
     *
     * @param string $predictionId
     * @param int $maxRetries
     * @return string
     * @throws Exception
     */
    protected function pollForCompletion(string $predictionId, int $maxRetries = 60): string
    {
        $retries = 0;

        while ($retries < $maxRetries) {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->replicateApiToken,
            ])->get($this->replicateApiUrl . '/predictions/' . $predictionId);

            if (!$response->successful()) {
                throw new Exception('Failed to check prediction status: ' . $response->body());
            }

            $prediction = $response->json();
            $status = $prediction['status'];

            if ($status === 'succeeded') {
                $output = $prediction['output'];
                if (is_array($output) && !empty($output[0])) {
                    return $output[0];
                }
                throw new Exception('Invalid output format from Minimax model');
            }

            if ($status === 'failed') {
                $error = $prediction['error'] ?? 'Unknown error';
                throw new Exception('Prediction failed: ' . $error);
            }

            // Still processing, wait and retry
            sleep(3);
            $retries++;
        }

        throw new Exception('Prediction timed out after ' . ($maxRetries * 3) . ' seconds');
    }

    /**
     * Get available archetypes
     *
     * @return array
     */
    public function getAvailableArchetypes(): array
    {
        return [
            [
                'value' => 'viking',
                'label' => '⚔️ Viking Warrior',
                'description' => 'Transform into a fierce Nordic warrior with authentic armor and weapons'
            ],
            [
                'value' => 'royal',
                'label' => '👑 Medieval King/Queen',
                'description' => 'Become a majestic medieval ruler with crown and royal regalia'
            ],
            [
                'value' => 'norse',
                'label' => '⚡ Norse God/Goddess',
                'description' => 'Ascend as a powerful deity from Norse mythology with divine powers'
            ]
        ];
    }

    /**
     * Get archetype prompts
     *
     * @return array
     */
    /**
     * Check prediction status and return progress info
     *
     * @param string $predictionId
     * @return array
     * @throws Exception
     */
    public function checkPredictionStatus(string $predictionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->replicateApiToken,
            ])->get($this->replicateApiUrl . '/predictions/' . $predictionId);

            if (!$response->successful()) {
                throw new Exception('Failed to check prediction status: ' . $response->body());
            }

            $prediction = $response->json();
            $status = $prediction['status'];

            // Calculate progress based on status
            $progress = 0;
            $message = '';

            switch ($status) {
                case 'starting':
                    $progress = 10;
                    $message = 'Initializing AI model...';
                    break;
                case 'processing':
                    $progress = 50;
                    $message = 'AI is creating your masterpiece...';
                    break;
                case 'succeeded':
                    $progress = 100;
                    $message = 'Generation complete!';
                    break;
                case 'failed':
                    $progress = 0;
                    $message = 'Generation failed';
                    break;
                default:
                    $progress = 25;
                    $message = 'Preparing generation...';
            }

            return [
                'status' => $status,
                'progress' => $progress,
                'message' => $message,
                'output' => $status === 'succeeded' ? $prediction['output'] : null,
                'error' => $status === 'failed' ? ($prediction['error'] ?? 'Unknown error') : null
            ];

        } catch (Exception $e) {
            Log::error('Error checking prediction status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate custom image using user-provided prompt
     *
     * @param string $prompt
     * @param \Illuminate\Http\UploadedFile|null $subjectImageFile
     * @param int $retries
     * @return string
     * @throws Exception
     */
    public function generateCustomImage(string $prompt, $subjectImageFile = null, int $retries = 3): array
    {
        try {
            $input = [
                'prompt' => $prompt,
                'aspect_ratio' => '1:1',
                'number_of_images' => 1,
                'prompt_optimizer' => true,
            ];

            // If user uploaded an image, use it as subject reference
            if ($subjectImageFile) {
                $processedImageData = $this->processImageForReplicate($subjectImageFile);
                $input['subject_reference'] = $processedImageData;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->replicateApiToken,
                'Content-Type' => 'application/json',
            ])->post($this->replicateApiUrl . '/predictions', [
                'version' => 'minimax/image-01',
                'input' => $input
            ]);

            if (!$response->successful()) {
                throw new Exception('Failed to create prediction: ' . $response->body());
            }

            $prediction = $response->json();
            $predictionId = $prediction['id'];

            Log::info("Custom prediction created: {$predictionId}");

            // Return prediction ID for frontend to poll
            return [
                'prediction_id' => $predictionId,
                'status' => 'starting',
                'message' => 'AI generation started successfully'
            ];

        } catch (Exception $e) {
            Log::error('Custom image generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getArchetypePrompts(): array
    {
        return [
            'viking' => 'A fierce Viking warrior with detailed horned helmet, authentic chainmail armor, leather bracers, braided beard, holding a battle axe, standing in a misty Nordic fjord with dramatic cinematic lighting, photorealistic, highly detailed',
            'royal' => 'A majestic medieval monarch with ornate golden crown adorned with precious gems, rich royal robes with ermine trim, jeweled scepter, standing in an opulent throne room with tapestries and stained glass, regal pose, royal portrait style, highly detailed',
            'norse' => 'A powerful Norse deity with divine armor crackling with lightning, mystical hammer glowing with runic symbols, ethereal godlike aura, standing in Asgard with rainbow bridge and epic storm clouds, fantasy art style, highly detailed',
        ];
    }

    /**
     * Upload generated image as artwork
     *
     * @param string $imageUrl
     * @param string $archetype
     * @param string $title
     * @param string $description
     * @param string|null $customPrompt
     * @return array
     * @throws Exception
     */
    public function uploadGeneratedImageAsArtwork(string $imageUrl, string $archetype, string $title, string $description, ?string $customPrompt = null): array
    {
        try {
            // Download the image
            $imageResponse = Http::timeout(30)->get($imageUrl);

            if (!$imageResponse->successful()) {
                throw new Exception('Failed to download generated image');
            }

            $imageData = $imageResponse->body();
            $filename = 'ai-generated-' . $archetype . '-' . time() . '.jpg';

            // Store the image
            $imagePath = 'artworks/' . $filename;
            Storage::disk('public')->put($imagePath, $imageData);

            // Get default category or create one for AI generated content
            $category = Category::where('name', 'AI Generated')->first();
            if (!$category) {
                $category = Category::create([
                    'name' => 'AI Generated',
                    'slug' => 'ai-generated',
                    'description' => 'Artwork generated using artificial intelligence',
                    'is_active' => true
                ]);
            }

            // Create artwork record
            $artwork = Artwork::create([
                'title' => $title,
                'description' => $description,
                'image_path' => $imagePath,
                'primary_image_path' => $imagePath,
                'category_id' => $category->id,
                'user_id' => auth()->id(),
                'style' => 'other',
                'technique' => 'digital',
                'location_text' => 'AI Generated',
                'is_commissioned' => false,
                'created_date' => now()->format('Y-m-d'),
                'is_active' => true,
                'is_featured' => false,
                'view_count' => 0,
                'like_count' => 0,
                'comment_count' => 0,
                'ai_generated' => true,
                'ai_archetype' => $archetype,
                'ai_service' => 'Minimax Image-01',
                'ai_prompt' => $customPrompt
            ]);

            return [
                'artwork' => $artwork->load('category', 'user'),
                'image_url' => asset('storage/' . $imagePath)
            ];

        } catch (Exception $e) {
            Log::error('Error uploading AI generated artwork: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate image file
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return array
     */
    public function validateImageFile($file): array
    {
        $maxSize = 10 * 1024 * 1024; // 10MB
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

        if (!$file) {
            return ['valid' => true];
        }

        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return [
                'valid' => false,
                'error' => 'Please select a valid image file (JPEG, PNG, or WebP)'
            ];
        }

        if ($file->getSize() > $maxSize) {
            return [
                'valid' => false,
                'error' => 'Image file size must be less than 10MB'
            ];
        }

        return ['valid' => true];
    }
}
