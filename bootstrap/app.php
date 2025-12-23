<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // SECURITY: Custom exception handling for production
        // Prevents leaking sensitive error details to users
        $exceptions->render(function (\Throwable $e, $request) {
            // Only customize for all requests in production
            if (!$request->expectsJson()) {
                return null;
            }

            // In production, hide sensitive error details
            if (app()->environment('production')) {
                // Log the full error for debugging
                \Illuminate\Support\Facades\Log::error('Exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]);

                // Return generic error for validation exceptions
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return null; // Let Laravel handle validation errors normally
                }

                // Return generic error for authentication exceptions
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'message' => 'Unauthenticated.',
                    ], 401);
                }

                // Return generic error for authorization exceptions
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return response()->json([
                        'message' => 'Forbidden.',
                    ], 403);
                }

                // Return generic error for model not found
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json([
                        'message' => 'Resource not found.',
                    ], 404);
                }

                // Return generic error for all other exceptions
                return response()->json([
                    'message' => 'An unexpected error occurred. Please try again later.',
                ], 500);
            }

            return null;
        });
    })->create();
