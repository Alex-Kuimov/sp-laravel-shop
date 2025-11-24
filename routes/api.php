<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
// User authentication routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    // User routes
    Route::apiResource('users', UserController::class);

    // Category routes
    Route::apiResource('categories', CategoryController::class);

    // Article routes
    Route::apiResource('articles', ArticleController::class);

    // User logout
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

