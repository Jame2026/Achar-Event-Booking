<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordResetPinController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/health/redis', function () {
    $redisConnected = false;
    $redisMessage = null;
    $cacheRoundTrip = false;
    $cacheMessage = null;

    try {
        $pong = Redis::connection('default')->ping();
        $redisConnected = strtolower((string) $pong) === 'pong' || (string) $pong === '1';
        $redisMessage = (string) $pong;
    } catch (\Throwable $e) {
        $redisMessage = $e->getMessage();
    }

    try {
        $key = 'health:redis:'.uniqid('', true);
        Cache::put($key, 'ok', now()->addSeconds(30));
        $cacheRoundTrip = Cache::get($key) === 'ok';
        Cache::forget($key);
        if (! $cacheRoundTrip) {
            $cacheMessage = 'Cache round-trip failed.';
        }
    } catch (\Throwable $e) {
        $cacheMessage = $e->getMessage();
    }

    $healthy = $redisConnected && $cacheRoundTrip;

    return response()->json([
        'status' => $healthy ? 'ok' : 'degraded',
        'cache_store' => config('cache.default'),
        'redis_client' => config('database.redis.client'),
        'redis_connected' => $redisConnected,
        'redis_message' => $redisMessage,
        'cache_round_trip' => $cacheRoundTrip,
        'cache_message' => $cacheMessage,
    ], $healthy ? 200 : 503);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/password-reset/request', [PasswordResetPinController::class, 'requestPin']);
Route::post('/password-reset/verify', [PasswordResetPinController::class, 'verifyPin']);
Route::get('/user/profile', [UserController::class, 'profile']);
Route::post('/user/profile', [UserController::class, 'updateProfile']);

Route::apiResource('events', EventController::class)->only(['index', 'show']);

Route::get('events/{event}/bookings', [BookingController::class, 'indexByEvent']);
Route::get('events/{event}/availability', [BookingController::class, 'availability']);
Route::get('events/{event}/availability-calendar', [BookingController::class, 'availabilityCalendar']);
Route::get('bookings', [BookingController::class, 'publicIndex']);
Route::apiResource('bookings', BookingController::class)->only(['store']);
Route::get('notifications/bookings', [NotificationController::class, 'index']);
Route::patch('notifications/bookings/read-all', [NotificationController::class, 'markAllRead']);
Route::patch('notifications/bookings/{notification}/read', [NotificationController::class, 'markRead']);
Route::get('vendor/services', [VendorController::class, 'servicesByVendorId']);
Route::post('vendor/services', [VendorController::class, 'storeServiceByVendorId']);
Route::patch('vendor/services/{event}', [VendorController::class, 'updateServiceByVendorId']);
Route::delete('vendor/services/{event}', [VendorController::class, 'destroyServiceByVendorId']);
Route::get('vendor/bookings', [VendorController::class, 'bookingsByVendorId']);
Route::patch('vendor/bookings/{booking}/status', [VendorController::class, 'updateBookingStatusByVendorId']);

Route::prefix('vendor')->group(function () {
    Route::get('/chats', [ChatController::class, 'vendorIndex']);
    Route::post('/chats/{conversation}/messages', [ChatController::class, 'vendorSendMessage']);
});

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
