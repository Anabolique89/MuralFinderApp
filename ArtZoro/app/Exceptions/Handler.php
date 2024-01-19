<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;



class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'error' => 'Unauthenticated',
        ], 401);
    }

    /*
     * return unauthenticated for non logged in users
     */

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof MethodNotAllowedHttpException) {
            $method = $request->getMethod();


            return response()->json([
                'success' => false,
                'error' => "{$method} method not allowed for this route",
            ], 405);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'error' => 'Unauthenticated',
            ], 401);
        }

        if ($exception instanceof UnauthorizedHttpException || $exception instanceof UnauthorizedException) {
            return response()->json([
                'error' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'success' => false,
            'error' => $exception->getMessage(),
        ], $exception->getCode() ?: 400);

    }
}