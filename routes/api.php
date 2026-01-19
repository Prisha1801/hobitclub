<?php

use App\Http\Controllers\Admin\WorkerController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\AuthController;

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

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
