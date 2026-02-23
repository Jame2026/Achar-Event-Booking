<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::apiResource('events', EventController::class);

Route::get('events/{event}/bookings', [BookingController::class, 'indexByEvent']);
Route::apiResource('bookings', BookingController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
