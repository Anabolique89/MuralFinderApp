<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class LoginApiController extends ApiBaseController
{
    public function __invoke(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $loginField = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (!Auth::attempt([$loginField => $credentials['email'], 'password' => $credentials['password']])) {
            return $this->sendError('Invalid email | username or password', JsonResponse::HTTP_UNAUTHORIZED);
        }

        $user = $request->user();

        if (!$user->hasVerifiedEmail()) {
            return $this->sendError("Email not verified", JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Dispatch the login event
        Event::dispatch('user.login', $user);

        $token = $user->createToken('authToken')->plainTextToken;

        $user->load('profile');

        $responseData = [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->profile->first_name,
                'last_name' => $user->profile->last_name,
                'username' => $user->username,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'role' => $user->role,
                'profile_image_url' => $user->profile->profile_image_url,
            ],
        ];

        return $this->sendSuccess($responseData, 'Login successful');
    }
}
