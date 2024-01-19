<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Profile;
use App\Models\User;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterApiController extends ApiBaseController
{
    public function __invoke(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:255',
                'role' => 'required|string|max:20',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed|min:8',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
            }

            $user = User::create([
                'username' => $request->username,
                'role' => $request->role,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            
            $user->sendEmailVerificationNotification();
            return $this->sendSuccess($user, 'Account Created, please confirm your email');
        } catch (\Throwable $th) {
            logger()->error($th->getMessage());
            return $this->sendError('Something went wrong on our end, we will check: ' . $th->getMessage());
        }
    }

}