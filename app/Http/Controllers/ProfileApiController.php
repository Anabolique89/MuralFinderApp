<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
            Log::error('Error creating profile: ' . $e->getMessage());
            return $this->sendError('Internal Server Error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Retrieve the user with profile
            $user = User::with('profile')->findOrFail($id);

            // Check authorization
            // if ($user->role !==$user->id !== auth()->id() || auth()->id() !== $request->user_id) {
            //     return $this->sendError("Cannot update another person's profile");
            // }

            // Validate request data
            $validator = Validator::make($request->all(), [
                'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
                'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
                'role' => 'nullable|string',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'password' => 'nullable|string|min:8|confirmed',
                'twitter' => 'nullable|string|max:255',
                'facebook' => 'nullable|string|max:255',
                'instagram' => 'nullable|string|max:255',
                'dob' => 'nullable|date',
                "bio" => 'nullable|string',
                "country" => 'nullable|string',
                "location" => 'nullable|string',
                'proffession' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray());
            }

            // Update user attributes
            $user->fill($request->only(['username', 'email', 'role']));

            // Update password if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            // Save the User model
            $user->save();

            // Update or create the Profile model
            $profileData = $request->only(['first_name', 'last_name',
            'twitter', 'facebook', 'instagram','dob', 'bio', 'country', 'proffession', 'location']);
            $profile = $user->profile;
            if ($profile) {
                $profile->update($profileData);
            } else {
                $profile = Profile::create(array_merge($profileData, ['user_id' => $user->id]));
            }

            return $this->sendSuccess($user, "Profile successfully updated");
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return $this->sendError('Internal Server Error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function show($id)
    {
        try {
            $user = User::withTrashed()
                ->with('profile')
                ->withCount('followers')
                ->withCount('followings')
                ->find($id);

            if (!$user) {
                return $this->sendError('User not found', JsonResponse::HTTP_NOT_FOUND);
            }

            return $this->sendSuccess($user, "User profile fetched");
        } catch (\Exception $e) {
            Log::error('Error fetching user profile: ' . $e->getMessage());
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
            Log::error('Error deleting profile: ' . $e->getMessage());
            return $this->sendError('Internal Server Error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadProfileImage(Request $request, $id)
    {
        try {
            $user = User::with('profile')->find($id);

            if (!$user) {
                return $this->sendError('user not found', JsonResponse::HTTP_NOT_FOUND);
            }

            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->sendError('The image is either null or does not pass the following rules: jpeg, png, jpg, gif, max:2048', JsonResponse::HTTP_BAD_REQUEST);
            }


            if ($request->hasFile('image')) {
                if ($user->profile->profile_image_url) {
                    $existingImagePath = str_replace('/storage', '', $user->profile->profile_image_url);
                    Storage::disk('public')->delete($existingImagePath);
                }

                // Upload the new image
                $path = $this->uploadImage($request->file('image'), 'uploads/profiles/', 'public');

                $user->profile->profile_image_url = $path;
                $user->profile->save();
            } else {
                $this->sendError("Image is required");
            }

            return $this->sendSuccess($path, 'Profile image uploaded successfully');
        } catch (\Exception $e) {
            Log::error('Error uploading profile image: ' . $e->getMessage());
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

    public function deleteUser($id)
{
    try {
        $user = User::find($id);

        if (!$user) {
            return $this->sendError('User not found', JsonResponse::HTTP_NOT_FOUND);
        }

        // Delete the user (this will automatically delete related records due to ON DELETE CASCADE)
        $user->delete();

        return $this->sendSuccess(null, 'User account and all related data have been deleted');
    } catch (\Exception $e) {
        Log::error('Error deleting user account: ' . $e->getMessage());
        return $this->sendError('Internal Server Error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}

public function updateRole(Request $request, $id){

    $validator = Validator::make($request->all(), [
        'role' => 'required|string',
    ]);

    if ($validator->fails()) {
        return $this->sendError('The role is either null or does not pass the following rules: string', JsonResponse::HTTP_BAD_REQUEST);
    }

    $user = User::find($id);
    if (!$user) {
        return $this->sendError('User not found', JsonResponse::HTTP_NOT_FOUND);
    }

    $user->role = $request->role;
    $user->save();
    return $this->sendSuccess(null, 'Role updated successfully');
    }
}




