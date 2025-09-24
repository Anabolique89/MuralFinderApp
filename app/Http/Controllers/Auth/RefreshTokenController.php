<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use App\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RefreshTokenController extends ApiBaseController
{
    protected AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $request->user();
            $result = $this->authService->refreshToken($user, $request->refresh_token);
            
            return $this->sendSuccess($result, $result['message']);
        } catch (ValidationException $e) {
            return $this->sendError($e->errors(), JsonResponse::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            logger()->error('Token refresh error: ' . $e->getMessage());
            return $this->sendError('Token refresh failed. Please login again.', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
