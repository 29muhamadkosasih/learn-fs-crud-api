<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (AuthenticationException $e, Request $request): JsonResponse {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        });

        $this->renderable(function (AuthorizationException $e, Request $request): JsonResponse {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden.',
                ], 403);
            }

            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
