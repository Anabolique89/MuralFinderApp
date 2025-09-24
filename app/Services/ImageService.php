<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Artwork;

class ImageService
{
    protected string $disk = 'public';
    protected array $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    protected int $maxFileSize = 10 * 1024 * 1024; // 10MB

    /**
     * Upload profile image
     */
    public function uploadProfileImage(UploadedFile $file, int $userId): string
    {
        $this->validateImage($file);

        $filename = $this->generateFilename($file, 'profile');
        $path = "profiles/{$userId}/{$filename}";

        // Store the file directly without resizing
        $path = $file->storeAs("profiles/{$userId}", $filename, $this->disk);

        return $path;
    }

    /**
     * Upload artwork image
     */
    public function uploadArtworkImage(UploadedFile $file, int $userId): string
    {
        $this->validateImage($file);

        $filename = $this->generateFilename($file, 'artwork');

        // Store the file directly
        $path = $file->storeAs("artworks/{$userId}", $filename, $this->disk);

        return $path;
    }

    /**
     * Upload wall image
     */
    public function uploadWallImage(UploadedFile $file, int $userId): string
    {
        $this->validateImage($file);

        $filename = $this->generateFilename($file, 'wall');

        // Store the file directly
        $path = $file->storeAs("walls/{$userId}", $filename, $this->disk);

        return $path;
    }

    /**
     * Upload post image
     */
    public function uploadPostImage(UploadedFile $file, int $userId): string
    {
        $this->validateImage($file);

        $filename = $this->generateFilename($file, 'post');

        // Store the file directly
        $path = $file->storeAs("posts/{$userId}", $filename, $this->disk);

        return $path;
    }

    /**
     * Create artwork image sizes (simplified - just store original)
     */
    protected function createArtworkImageSizes(UploadedFile $file, string $basePath): void
    {
        // For now, just store the original file
        // In the future, you can add image resizing using GD or ImageMagick
        $pathInfo = pathinfo($basePath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];

        // Store original file
        Storage::disk($this->disk)->putFileAs($directory, $file, "{$filename}.{$extension}");
    }

    /**
     * Delete artwork images
     */
    public function deleteArtworkImages(Artwork $artwork): void
    {
        if ($artwork->primary_image_path) {
            $this->deleteImageSizes($artwork->primary_image_path);
        }

        if ($artwork->images) {
            foreach ($artwork->images as $imagePath) {
                $this->deleteImageSizes($imagePath);
            }
        }
    }

    /**
     * Delete image and all its sizes
     */
    protected function deleteImageSizes(string $imagePath): void
    {
        $pathInfo = pathinfo($imagePath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];

        $sizes = ['', '_large', '_medium', '_thumb'];

        foreach ($sizes as $size) {
            $path = "{$directory}/{$filename}{$size}.jpg";
            if (Storage::disk($this->disk)->exists($path)) {
                Storage::disk($this->disk)->delete($path);
            }
        }
    }

    /**
     * Get image URL
     */
    public function getImageUrl(string $path, string $size = ''): string
    {
        if (!$path) {
            return '';
        }

        if ($size) {
            $pathInfo = pathinfo($path);
            $directory = $pathInfo['dirname'];
            $filename = $pathInfo['filename'];
            $path = "{$directory}/{$filename}_{$size}.jpg";
        }

        return asset('storage/' . $path);
    }

    /**
     * Validate uploaded image
     */
    protected function validateImage(UploadedFile $file): void
    {
        if (!in_array($file->getMimeType(), $this->allowedMimes)) {
            throw new \InvalidArgumentException('Invalid image type. Allowed types: JPEG, PNG, GIF, WebP');
        }

        if ($file->getSize() > $this->maxFileSize) {
            throw new \InvalidArgumentException('Image size too large. Maximum size: 10MB');
        }
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(UploadedFile $file, string $prefix = ''): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);

        return $prefix ? "{$prefix}_{$timestamp}_{$random}.{$extension}" : "{$timestamp}_{$random}.{$extension}";
    }
}
