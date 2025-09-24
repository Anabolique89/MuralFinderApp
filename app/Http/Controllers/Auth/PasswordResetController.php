<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\User;
use Hash;
use Illuminate\Auth\Events\PasswordReset;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash as FacadesHash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Str;

class PasswordResetController extends ApiBaseController
{


    public function changePassword(Request $request)
    {
        $user = $request->user();

        if (!FacadesHash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Current password is incorrect', Response::HTTP_BAD_REQUEST);
        }

        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $user->password = FacadesHash::make($request->new_password);
        $user->save();

        return $this->successResponse('Password changed successfully', null, Response::HTTP_OK);
    }

    public function sendPasswordResetToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray());
            }

            $status = Password::sendResetLink(
                $request->only('email'),
                function ($user, $token) {
                    $shortenedToken = Str::random(10);

                    // Update the password_resets table with the generated token
                    DB::table('password_resets')->updateOrInsert(
                        ['email' => $user->email],
                        ['token' => $shortenedToken, 'created_at' => now()]
                    );

                    // Notify the user with the same token
                    $user->notify(new CustomResetPasswordNotification($shortenedToken));
                }
            );

            if ($status === Password::RESET_LINK_SENT) {
                return $this->sendSuccess(null, 'Password reset link sent');
            }

            // Handle the case where the password reset link could not be sent
            return $this->sendError('Unable to send password reset link', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), $e->getCode());
        }
    }



    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray());
            }

            // Check if the token matches in the password_resets_tokens table
            $passwordReset = DB::table('password_resets')
                ->where('email', $request->email)
                ->where('token', $request->token)
                ->first();

            if (!$passwordReset) {
                return $this->sendError('Invalid token', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = User::where('email', $request->email)->first();


            $user->password = FacadesHash::make($request->password);
            $user->save();

            // Delete the used token from the password_resets_tokens table
            DB::table('password_resets')
                ->where('email', $user->email)
                ->delete();

            event(new PasswordReset($user));

            return $this->sendSuccess(null, 'Password reset successful');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->sendError($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
