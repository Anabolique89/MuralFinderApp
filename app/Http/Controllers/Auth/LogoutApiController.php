<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use App\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutApiController extends ApiBaseController
{
    protected AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $currentToken = $request->bearerToken();

            $result = $this->authService->logout($user, $currentToken);

            return $this->sendSuccess($result, $result['message']);
        } catch (\Exception $e) {
            logger()->error('Logout error: ' . $e->getMessage());
            return $this->sendError('Logout failed. Please try again.', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
