<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Validator;

class ProfileApiController extends ApiBaseController
{

    public function create(Request $request)
    {
        try {
            // Check if a profile already exists for the user
            $existingProfile = Profile::where('user_id', $request->input('user_id'))->first();
            if ($existingProfile) {
                return $this->sendError('User already has a profile', JsonResponse::HTTP_CONFLICT);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'bio' => 'nullable|string',
                'age' => 'nullable|integer',
                'country' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray());
            }

            $profile = Profile::create($request->all());

            return $this->sendSuccess($profile, "Profile created");
        } catch (\Exception $e) {
            \Log::error('Error creating profile: ' . $e->getMessage());
            return $this->sendError('Internal Server Error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $profile = Profile::find($id);

            if (!$profile) {
                return $this->sendError('Profile not found', JsonResponse::HTTP_NOT_FOUND);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'exists:users,id',
                'first_name' => 'string',
                'last_name' => 'string',
                'bio' => 'nullable|string',
                'dob' => 'nullable|date',
                'country' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray());
            }

            $profile->update($request->all());

            return $this->sendSuccess($profile, "Profile successfully updated");
        } catch (\Exception $e) {
            \Log::error('Error updating profile: ' . $e->getMessage());
            return $this->sendError('Internal Server Error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $profile = Profile::with('user')->where('user_id', $id)->get();

            if (!$profile) {
                return $this->sendError('Profile not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($profile, "Profile fetched");
        } catch (\Exception $e) {
            \Log::error('Error fetching profile: ' . $e->getMessage());
            return $this->sendError('Internal Server Error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $profile = Profile::find($id);

            if (!$profile) {
                return $this->sendError('Profile not found', JsonResponse::HTTP_NOT_FOUND);
            }

            $profile->delete();

            return $this->sendSuccess(null, 'Profile has been deleted');
        } catch (\Exception $e) {
            \Log::error('Error deleting profile: ' . $e->getMessage());
            return $this->sendError('Internal Server Error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadProfileImage(Request $request, $id)
    {
        try {
            $profile = Profile::find($id);

            if (!$profile) {
                return $this->sendError('Profile not found', JsonResponse::HTTP_NOT_FOUND);
            }

            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();

            // Store the image in the 'profile_images' directory within the public disk
            Storage::disk('public')->putFileAs('profile_images', $image, $imageName);

            // Update the profile's image_filename attribute
            $profile->update(['profile_image_url' => $imageName]);

            return $this->sendSuccess(null, 'Profile image uploaded successfully');
        } catch (\Exception $e) {
            \Log::error('Error uploading profile image: ' . $e->getMessage());
            return $this->sendError('Internal Server Error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
