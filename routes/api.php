<?php

use App\Http\Controllers\Admin\WorkerController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Webhooks\WhatsappBookingWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Api\Worker\WorkerAuthController;
use App\Http\Controllers\Api\Worker\WorkerProfileController;
use App\Http\Controllers\Api\Admin\AdminWorkerController;

/*
|--------------------------------------------------------------------------
| Worker APIs
|--------------------------------------------------------------------------
*/
Route::post('/worker/register', [WorkerAuthController::class, 'register']);

Route::middleware(['auth:sanctum', 'role:worker'])->group(function () {
    Route::get('/worker/me', [WorkerProfileController::class, 'me']);
    Route::post('/worker/profile/update', [WorkerProfileController::class, 'update']);
    Route::post('/worker/kyc/upload', [WorkerProfileController::class, 'uploadKyc']);
});

/*
|--------------------------------------------------------------------------
| Admin APIs
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/admin/workers/{worker}/approve-kyc', [AdminWorkerController::class, 'approveKyc']);
    Route::post('/admin/workers/{worker}/reject-kyc', [AdminWorkerController::class, 'rejectKyc']);
});

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

});
