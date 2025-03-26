<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\DeliveryController;
use App\Http\Controllers\API\StatusProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('products/chart', [ProductController::class, 'getChartData']);

    Route::apiResource('products', ProductController::class);
    Route::apiResource('deliveries', DeliveryController::class);
    Route::apiResource('status-products', StatusProductController::class);

    Route::post('/logout', [AuthController::class, 'logout']);
});
