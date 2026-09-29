<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        //
    })

    ->withExceptions(function (Exceptions $exceptions): void {

        /*
         * Force API requests to always return JSON errors.
         *
         * This prevents Laravel from returning
         * HTML error pages for API requests.
         */
        $exceptions->shouldRenderJsonWhen(
            function (Request $request, \Throwable $exception): bool {
                return $request->is('api/*')
                    || $request->expectsJson();
            }
        );

        /*
         * Handle validation errors.
         *
         * Example:
         * Missing required name or phone.
         */
        $exceptions->render(
            function (
                ValidationException $exception,
                Request $request
            ) {
                if (! $request->is('api/*')) {
                    return null;
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'data' => null,
                    'errors' => $exception->errors(),
                ], 422);
            }
        );

        /*
         * Handle API routes or resources
         * that cannot be found.
         */
        $exceptions->render(
            function (
                NotFoundHttpException $exception,
                Request $request
            ) {
                if (! $request->is('api/*')) {
                    return null;
                }

                return response()->json([
                    'success' => false,
                    'message' => 'The requested resource was not found.',
                    'data' => null,
                ], 404);
            }
        );
    })

    ->create();
