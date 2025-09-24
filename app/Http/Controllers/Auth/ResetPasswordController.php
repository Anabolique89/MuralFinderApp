<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use App\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends ApiBaseController
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
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->authService->resetPassword(
                $request->email,
                $request->token,
                $request->password
            );
            
            return $this->sendSuccess($result, $result['message']);
        } catch (ValidationException $e) {
            return $this->sendError($e->errors(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            logger()->error('Password reset error: ' . $e->getMessage());
            return $this->sendError('Password reset failed. Please try again.', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
