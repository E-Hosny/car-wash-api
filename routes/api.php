<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CarController;
use App\Http\Controllers\API\CarYearController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;






// ✅ استرجاع بيانات المستخدم المسجل دخول
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ✅ التسجيل وتسجيل الدخول
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ✅ تسجيل الخروج (يتطلب توكن)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('services', ServiceController::class);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/my', [OrderController::class, 'myOrders']);
    Route::get('/orders/available', [OrderController::class, 'availableOrders']);

    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/accept', [OrderController::class, 'accept']);
    Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    Route::get('/cars', [CarController::class, 'index']);
    Route::post('/cars', [CarController::class, 'store']);
    Route::delete('/cars/{car}', [CarController::class, 'destroy']);

});

Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{brand}/models', [BrandController::class, 'models']);
Route::get('/car-years', [CarYearController::class, 'index']);
