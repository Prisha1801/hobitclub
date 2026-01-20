<?php

use App\Http\Controllers\Admin\WorkerController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Location\CityController;
use App\Http\Controllers\Location\ZoneController;
use App\Http\Controllers\Location\ServiceableAreaController;
use App\Http\Controllers\LiveTracking\LiveTrackingController;
use App\Http\Controllers\Webhooks\WhatsappBookingWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminBookingController;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/admin/bookings/{booking}/approve-payment', [AdminBookingController::class, 'approvePayment']);
    Route::post('/admin/bookings/{booking}/reject-payment', [AdminBookingController::class, 'rejectPayment']);
});

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Route::post('/webhooks/whatsapp/booking', [WhatsappBookingWebhookController::class, 'handle']);
Route::post('/webhooks/whatsapp/booking', [WhatsappBookingWebhookController::class, 'handle'])
    ->middleware('throttle:30,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']); 
});
Route::middleware([
    'auth:sanctum',
    'role:admin,coordinator,operation_head'
])->prefix('admin')->group(function () {

    // WORKERS
    Route::get('workers', [WorkerController::class, 'index']);
    Route::post('workers', [WorkerController::class, 'store']);
    Route::get('workers/{id}', [WorkerController::class, 'show']);
    Route::patch('workers/{id}/status', [WorkerController::class, 'updateStatus']);
    Route::delete('workers/{id}', [WorkerController::class, 'destroy']);

    // CUSTOMERS
    Route::get('customers', [CustomerController::class, 'index']);
    Route::post('customers', [CustomerController::class, 'store']);
    Route::get('customers/{id}', [CustomerController::class, 'show']);
    Route::delete('customers/{id}', [CustomerController::class, 'destroy']);

    // ADMIN USERS (ONLY ADMIN SHOULD MANAGE ADMINS)
    Route::middleware('role:admin')->group(function () {
        Route::get('admins', [AdminUserController::class, 'index']);
        Route::post('admins', [AdminUserController::class, 'store']);
        Route::delete('admins/{id}', [AdminUserController::class, 'destroy']);
    });

    //Services
    Route::apiResource('service-categories', ServiceCategoryController::class);
    Route::apiResource('services', ServiceController::class);

    //location
    Route::apiResource('cities', CityController::class);
    Route::apiResource('zones', ZoneController::class);
    Route::apiResource('serviceable-areas', ServiceableAreaController::class);

    //livetracking
    Route::post('tracking/update', [LiveTrackingController::class, 'updateLocation']);
});
