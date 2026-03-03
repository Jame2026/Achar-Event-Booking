<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::apiResource('events', EventController::class)->only(['index', 'show']);

Route::get('events/{event}/bookings', [BookingController::class, 'indexByEvent']);
Route::get('events/{event}/availability', [BookingController::class, 'availability']);
Route::get('bookings', [BookingController::class, 'publicIndex']);
Route::apiResource('bookings', BookingController::class)->only(['store']);

Route::middleware(['auth', 'role:user,vendor,admin'])->prefix('user')->group(function () {
    Route::get('/me', [UserController::class, 'me']);
    Route::get('/bookings', [UserController::class, 'myBookings']);
});

Route::middleware(['auth', 'role:vendor,admin'])->prefix('vendor')->group(function () {
    Route::get('/dashboard', [VendorController::class, 'dashboard']);
    Route::get('/events', [VendorController::class, 'myEvents']);
    Route::post('/events', [VendorController::class, 'storeEvent']);
    Route::put('/events/{event}', [VendorController::class, 'updateEvent']);
    Route::patch('/events/{event}', [VendorController::class, 'updateEvent']);
    Route::delete('/events/{event}', [VendorController::class, 'destroyEvent']);
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::patch('/bookings/{booking}', [BookingController::class, 'update']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);
});
