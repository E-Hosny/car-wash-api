<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\CarController;
use App\Http\Controllers\API\CarYearController;
use App\Http\Controllers\API\FcmTokenController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\PackageController;
use App\Http\Controllers\API\ConfigController;
use App\Http\Controllers\API\TimeSlotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ✅ استرجاع بيانات المستخدم المسجل دخول
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ✅ التسجيل وتسجيل الدخول
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ✅ OTP Authentication
Route::post('/check-phone', [AuthController::class, 'checkPhone']);
Route::post('/login-with-otp', [AuthController::class, 'loginWithOtp']);

// ✅ Public endpoints (no authentication required)
Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{brand}/models', [BrandController::class, 'models']);
Route::get('/car-years', [CarYearController::class, 'index']);
Route::get('/config', [ConfigController::class, 'appConfig']);

// ✅ Public packages endpoint for guest browsing
Route::get('/packages', [PackageController::class, 'index']);



// ✅ Public services endpoint for guest browsing
Route::apiResource('services', ServiceController::class)->only(['index', 'show']);

// ✅ تسجيل الخروج (يتطلب توكن)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/multi-car', [OrderController::class, 'storeMultiCar']);
    Route::get('/orders/my', [OrderController::class, 'myOrders']);
    Route::get('/orders/available', [OrderController::class, 'availableOrders']);
    Route::get('/orders/booked-time-slots', [OrderController::class, 'getBookedTimeSlots']);
    Route::get('/orders/completed', [OrderController::class, 'completedOrders']);
    Route::get('/orders/accepted', [OrderController::class, 'acceptedOrders']);
    Route::get('/orders/inProgress', [OrderController::class, 'inProgressOrders']);
    Route::get('/orders/location/{id}', [OrderController::class, 'getLocation']);

    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/accept', [OrderController::class, 'accept']);
    Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/assign', [OrderController::class, 'assignToWorker']);

    //location
    Route::post('/orders/location', [OrderController::class, 'saveLocation']);

    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');

    Route::post('/fcm/save-token', [FcmTokenController::class, 'store']);

    Route::get('/cars', [CarController::class, 'index']);
    Route::post('/cars', [CarController::class, 'store']);
    Route::delete('/cars/{car}', [CarController::class, 'destroy']);

    Route::get('/workers',[UserController::class,'getWorkers']);

    // Payment routes
    Route::post('/payments/create-intent', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/payments/confirm', [PaymentController::class, 'confirmPayment']);
    Route::get('/payments/status', [PaymentController::class, 'getPaymentStatus']);
    Route::post('/orders/{orderId}/payment-status', [PaymentController::class, 'updateOrderPaymentStatus']);

    // Addresses API
    Route::get('/addresses', [\App\Http\Controllers\API\AddressController::class, 'index']);
    Route::post('/addresses', [\App\Http\Controllers\API\AddressController::class, 'store']);
    Route::delete('/addresses/{address}', [\App\Http\Controllers\API\AddressController::class, 'destroy']);

    // Protected Packages API (require authentication)
    Route::get('/packages/{id}', [PackageController::class, 'show']);
    Route::post('/packages/{id}/purchase', [PackageController::class, 'purchase']);
    Route::get('/packages/my/current', [PackageController::class, 'myPackage']);
    Route::get('/packages/my/services', [PackageController::class, 'availableServices']);
    Route::get('/packages/my/history', [PackageController::class, 'packageHistory']);

    // Time Slots API
    Route::get('/time-slots', [TimeSlotController::class, 'getTimeSlots']);
    Route::get('/time-slots/booked', [TimeSlotController::class, 'getBookedTimeSlots']);
    Route::post('/time-slots/book', [TimeSlotController::class, 'bookTimeSlot']);
});

// Admin Time Slots Management (Admin only)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/time-slots', [TimeSlotController::class, 'getManagementData']);
    Route::post('/admin/time-slots/toggle', [TimeSlotController::class, 'toggleTimeSlot']);
    Route::post('/admin/time-slots/release', [TimeSlotController::class, 'releaseTimeSlot']);
});

// Webhook route (لا يحتاج authentication)
Route::post('/webhooks/stripe', [PaymentController::class, 'handleWebhook']);
