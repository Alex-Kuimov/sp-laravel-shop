<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return App\Http\Responses\ApiResponse::notFound('Resource not found');
        });

        $exceptions->render(function (Illuminate\Auth\Access\AuthorizationException $e) {
            return App\Http\Responses\ApiResponse::unauthorized($e->getMessage() ?: 'Unauthorized');
        });

        $exceptions->render(function (Illuminate\Validation\ValidationException $e) {
            return App\Http\Responses\ApiResponse::validationError($e->errors(), $e->getMessage());
        });

        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return App\Http\Responses\ApiResponse::notFound('The requested resource was not found');
        });

        $exceptions->render(function (Illuminate\Database\QueryException $e) {
            if (app()->environment('local')) {
                return App\Http\Responses\ApiResponse::error('Database error: ' . $e->getMessage(), 500);
            }
            
            return App\Http\Responses\ApiResponse::error('An error occurred while processing your request', 500);
        });
    })->create();
