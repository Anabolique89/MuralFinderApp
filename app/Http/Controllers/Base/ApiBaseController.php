<?php

namespace App\Http\Controllers\Base;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ApiBaseController extends Controller
{
    protected $statusCode = JsonResponse::HTTP_OK;

    public function sendSuccess($data = [], $message = '', $code=200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $this->statusCode);
    }

    public function sendError($message = '', $code = JsonResponse::HTTP_BAD_REQUEST)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }


    public function validationError(array $errors, $code = JsonResponse::HTTP_BAD_REQUEST)
    {
        throw ValidationException::withMessages($errors);
    }

    public function setStatusCode($statusCode)
    {

        $this->statusCode = $statusCode;

        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    protected function uploadImage($file, $folder = 'uploads', $disk = 'public')
    {
        try {
            $path = $file->store($folder, $disk);
            return Storage::url($path);
        } catch (\Exception $e) {
            Log::error('Error uploading image: ' . $e->getMessage());
            return null;
        }
    }

    protected function deleteImage($imagePath)
    {
        try {
            $fullImagePath = public_path($imagePath);

            if (file_exists($fullImagePath)) {
                unlink($fullImagePath);
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Error deleting image: ' . $e->getMessage());
        }

        return false;
    }

}
