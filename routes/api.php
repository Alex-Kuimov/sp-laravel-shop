<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderProductController;
use App\Http\Controllers\OrderPaymentController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AuthController;

// Public routes
// User authentication routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [AuthController::class, 'reset']);

// Category routes
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);

// Product routes
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    // User routes
    Route::apiResource('users', UserController::class)->except(['store', 'index', 'show']);
    
    // Category routes
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    
    // Product routes
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    
    // Order routes
    Route::apiResource('orders', OrderController::class);
    
    // OrderPayment routes
    Route::apiResource('order-payments', OrderPaymentController::class);
    
    // Cart routes
    Route::apiResource('carts', CartController::class);
    
    // User logout
    Route::post('logout', [AuthController::class, 'logout']);
});