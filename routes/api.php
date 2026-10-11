<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PaymentController;


Route::prefix('v1')->group(function () {
    Route::post('payments/webhook', [PaymentController::class, 'webhook']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum') ;
    Route::get('/me', [App\Http\Controllers\AuthController::class, 'me'])->middleware('auth:sanctum') ;

    Route::get('categories', [CategoryController::class, 'index']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
});
Route::get('events', [EventController::class, 'index']);
Route::get('events/{event}', [EventController::class, 'show']);

Route::middleware(['auth:sanctum', 'role:organizer'])->group(function () {
    Route::post('events', [EventController::class, 'store']);
    Route::put('events/{event}', [EventController::class, 'update']);
    Route::delete('events/{event}', [EventController::class, 'destroy']);
});
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    Route::post('events/{event}/bookings', [BookingController::class, 'store']);
    Route::get('bookings', [BookingController::class, 'index']);
    Route::delete('bookings/{booking}', [BookingController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:organizer'])->group(function () {
    Route::get('events/{event}/bookings', [BookingController::class, 'eventBookings']);
});

Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    Route::post('bookings/{reference}/pay', [PaymentController::class, 'pay']);
});


Route::middleware(['auth:sanctum', 'role:organizer,admin'])->group(function () {
    Route::post('tickets/verify', [TicketController::class, 'verify']);
    Route::post('tickets/check-in', [TicketController::class, 'checkIn']);
    Route::get('events/{event}/attendance', [TicketController::class, 'attendance']);
});
});
