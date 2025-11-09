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

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    // User routes
    Route::apiResource('users', UserController::class);
    
    // Category routes
    Route::apiResource('categories', CategoryController::class);
    
    // Product routes
    Route::apiResource('products', ProductController::class);
    
    // Order routes
    Route::apiResource('orders', OrderController::class);
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus']);
    
    // OrderProduct routes
    Route::apiResource('order-products', OrderProductController::class);
    
    // OrderPayment routes
    Route::apiResource('order-payments', OrderPaymentController::class);
    
    // OrderHistory routes
    Route::apiResource('order-histories', OrderHistoryController::class);
    
    // Cart routes
    Route::apiResource('carts', CartController::class);
    
    // Create order from cart
    Route::post('carts/create-order', [CartController::class, 'createOrder']);
});