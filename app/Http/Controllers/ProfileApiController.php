<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Profile;
use App\Models\User;
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
            $profile = Profile::with('user')
                ->withCount('followers')
                ->withCount('followings')
                ->where('user_id', $id)
                ->first();

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

            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->sendError('The image is either null or does not pass the following rules: jpeg, png, jpg, gif, max:2048', JsonResponse::HTTP_BAD_REQUEST);
            }

            $image = $request->file('image');

            if ($image) {
                // Delete the previous image if it exists
                if ($profile->profile_image_url) {
                    $existingImagePath = str_replace('/storage', '', $profile->profile_image_url);
                    Storage::disk('public')->delete($existingImagePath);
                }

                // Upload the new image
                $path = $this->uploadImage($image, 'uploads/profiles/', 'public');

                $profile->profile_image_url = $path;
                $profile->save();
            } else {
                // No new image uploaded, keep existing path (if applicable)
                $path = $profile->profile_image_url;
            }

            return $this->sendSuccess($path, 'Profile image uploaded successfully');
        } catch (\Exception $e) {
            \Log::error('Error uploading profile image: ' . $e->getMessage());
            return $this->sendError('Internal Server Error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function search(Request $request)
    {
        $query = User::query();

        $role = $request->get('role');
        if ($role) {
            $query->where('role', $role);
        }

        $name = $request->get('name');
        if ($name) {
            $query->where('username', 'like', '%' . $name . '%')
                ->orWhere('email', 'like', '%' . $name . '%')
                ->orWhereHas('profile', function ($query) use ($name) {
                    $query->where('first_name', 'like', "% $name %")
                        ->orWhere('last_name', 'like', "% $name %");

                });
        }

        $users = $query->with('profile')->paginate(15);
        return $this->sendSuccess($users, 'users fetched');
    }


}
