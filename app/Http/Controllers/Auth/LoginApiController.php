<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Base\ApiBaseController;
use App\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LoginApiController extends ApiBaseController
{
    protected AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->authService->login(
                $request->email,
                $request->password,
                $request->boolean('remember', false)
            );

            return $this->sendSuccess($result, $result['message']);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $message = is_array($errors) ? collect($errors)->flatten()->first() : $errors;
            return $this->sendError($message, JsonResponse::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            logger()->error('Login error: ' . $e->getMessage());
            return $this->sendError('Login failed. Please try again.', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
