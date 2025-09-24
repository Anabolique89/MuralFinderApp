<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use App\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends ApiBaseController
{
    protected AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->authService->sendPasswordResetLink($request->email);
            return $this->sendSuccess($result, $result['message']);
        } catch (\Exception $e) {
            logger()->error('Password reset request error: ' . $e->getMessage());
            return $this->sendError('Failed to send password reset link. Please try again.', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
