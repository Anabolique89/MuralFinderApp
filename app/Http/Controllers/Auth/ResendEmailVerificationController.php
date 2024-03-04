<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ResendEmailVerificationController extends ApiBaseController
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->sendError('User not found', JsonResponse::HTTP_NOT_FOUND);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->sendError('Email already verified', JsonResponse::HTTP_BAD_REQUEST);
        }

        $user->sendEmailVerificationNotification();

        return $this->sendSuccess($request->email, 'Verification email resent');
    }
}