<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Throwable;
use Illuminate\Validation\ValidationException;

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

    public function render($request, Throwable $exception): Response|JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            // Handle validation errors
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof AuthenticationException) {
                return $this->unauthenticated($request, $exception);
            }

            // Handle custom domain exceptions
            if ($exception instanceof \App\Exceptions\OutOfStockException ||
                $exception instanceof \App\Exceptions\InsufficientStockException ||
                $exception instanceof \App\Exceptions\CartException) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], $exception->getCode() ?: 400);
            }

            $status = 500;
            $message = 'Server Error';

            if (method_exists($exception, 'getStatusCode')) {
                $status = $exception->getStatusCode();
            }
            if (method_exists($exception, 'getMessage') && $exception->getMessage()) {
                $message = $exception->getMessage();
            }

            return response()->json([
                'message' => $message,
            ], $status);
        }

        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        return redirect()->guest(route('login'));
    }


    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
