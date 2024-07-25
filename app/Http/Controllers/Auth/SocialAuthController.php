<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;

class SocialAuthController extends ApiBaseController
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            $existingUser = User::whereEmail($socialUser->getEmail())->first();

            if ($existingUser) {

                $token = $existingUser->createToken('authToken')->plainTextToken;

                $existingUser->load('profile');

                $responseData = [
                    'token' => $token,
                    'user' => [
                        'id' => $existingUser->id,
                        'first_name' => $existingUser->profile->first_name ?? null,
                        'last_name' => $existingUser->profile->last_name ?? null,
                        'username' => $existingUser->username,
                        'email' => $existingUser->email,
                        'created_at' => $existingUser->created_at,
                        'updated_at' => $existingUser->updated_at,
                        'role' => $existingUser->role ?? null,
                        'profile_image_url' => $existingUser->profile->profile_image_url ?? null,

                    ],
                ];

                $redirectUrl = 'https://www.muralfinder.net/login?token=' . urlencode($responseData['token']) . '&user=' . urlencode(json_encode($responseData['user']));
                return redirect()->to($redirectUrl);
            } else {
                // User does not exist, create a new one

                $nameParts = explode(' ', $socialUser->getName());
                $username = $nameParts[0];
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'username' => $username,
                    'password' => Hash::make('password')
                ]);

                if (!$user->profile) {
                    Profile::create([
                        'user_id' => $user->id,
                        'first_name' => $socialUser->getName(),
                    ]);
                }

                $token = $user->createToken('authToken')->plainTextToken;

                $user->load('profile');

                $responseData = [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->profile->first_name ?? null,
                        'last_name' => $user->profile->last_name ?? null,
                        'username' => $user->username,
                        'email' => $user->email,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                        'role' => $user->role ?? null
                    ],
                ];

                $redirectUrl = 'https://www.muralfinder.net/login?token=' . urlencode($responseData['token']) . '&user=' . urlencode(json_encode($responseData['user']));
                return redirect()->to($redirectUrl);
            }
        } catch (\Exception $e) {
            return $this->sendError('Unable to authenticate using ' . $provider . ': ' . $e->getMessage(), JsonResponse::HTTP_UNAUTHORIZED);
        }
    }
}
