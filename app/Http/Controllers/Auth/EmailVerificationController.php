<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use App\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends ApiBaseController
{
    protected AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Verify email address
     */
    public function verify(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->authService->verifyEmail($user);

            return $this->sendSuccess($result, $result['message']);
        } catch (\Exception $e) {
            logger()->error('Email verification error: ' . $e->getMessage());
            return $this->sendError('Email verification failed. Please try again.', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Resend verification email
     */
    public function resend(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                return $this->sendError('Email already verified.', JsonResponse::HTTP_BAD_REQUEST);
            }

            $user->sendEmailVerificationNotification();

            return $this->sendSuccess(null, 'Verification email sent successfully.');
        } catch (\Exception $e) {
            logger()->error('Email verification resend error: ' . $e->getMessage());
            return $this->sendError('Failed to send verification email. Please try again.', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Legacy verification endpoint (for email links)
     */
    public function __invoke(Request $request)
    {
        try {
            $user = \App\Models\User::find($request->id);

            if (!$user) {
                return redirect()->intended(config('app.frontend_url') . '/login?verified=0');
            }

            if ($user->markEmailAsVerified()) {
                event(new \Illuminate\Auth\Events\Verified($user));
            }

            return redirect()->intended(config('app.frontend_url') . '/login?verified=1');
        } catch (\Exception $e) {
            logger()->error('Email verification error: ' . $e->getMessage());
            return redirect()->intended(config('app.frontend_url') . '/login?verified=0');
        }
    }
}
