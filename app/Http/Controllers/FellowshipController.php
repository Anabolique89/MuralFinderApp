<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

class FellowshipController extends ApiBaseController
{
    public function followUser(Request $request)
    {
        $follower = $request->user();
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $followedUser = User::find($validatedData['user_id']);

        // Check if the user is not already being followed
        if (!$followedUser || $follower->isFollowing($followedUser)) {
            return $this->sendError('Unable to follow user. User is already being followed.');
        }

        // Follow the user
        $follower->follow($followedUser);

        return $this->sendSuccess(null, 'User followed successfully.');
    }


    public function unfollowUser(Request $request)
    {
        $follower = $request->user();
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $followedUser = User::find($validatedData['user_id']);

        // Check if the user is being followed
        if (!$followedUser || !$follower->isFollowing($followedUser)) {
            return $this->sendError('Unable to unfollow user. User is not being followed.', JsonResponse::HTTP_BAD_REQUEST);
        }

        // Unfollow the user
        $follower->unfollow($followedUser);

        return $this->sendSuccess(null, 'User unfollowed successfully.');
    }



    public function getUserFollowers(Request $request)
    {
        $user = $request->user();
        $followers = $user->followers;

        return $this->sendSuccess($followers, 'User followers retrieved successfully.');
    }

    public function getUserFollowings(Request $request)
    {
        $user = $request->user();
        $followings = $user->followings;

        return $this->sendSuccess($followings, 'User followings retrieved successfully.');
    }

}
