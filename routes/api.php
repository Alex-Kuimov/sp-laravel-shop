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
Route::post('/auth/password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/auth/password/reset', [AuthController::class, 'reset']);

// Category routes
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);

// Article routes
Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{article}', [ArticleController::class, 'show']);

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    // User routes
    Route::apiResource('users', UserController::class);

    // Category routes
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);

    // Article routes
    Route::apiResource('articles', ArticleController::class)->except(['index', 'show']);

    // User logout
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
