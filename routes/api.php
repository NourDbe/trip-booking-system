<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\TripController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Trip API Routes
|--------------------------------------------------------------------------
*/

Route::get('/trips', [
    TripController::class,
    'index',
]);

Route::get('/trips/{tripId}', [
    TripController::class,
    'show',
])->whereNumber('tripId');

Route::get('/trips/{tripId}/available-seats', [
    BookingController::class,
    'availableSeats',
])->whereNumber('tripId');

/*
|--------------------------------------------------------------------------
| Booking API Routes
|--------------------------------------------------------------------------
*/

Route::post('/trips/{tripId}/bookings', [
    BookingController::class,
    'store',
])->whereNumber('tripId');

Route::get('/bookings/{bookingId}', [
    BookingController::class,
    'show',
])->whereNumber('bookingId');

Route::patch('/bookings/{bookingId}/confirm', [
    BookingController::class,
    'confirm',
])->whereNumber('bookingId');

Route::patch('/bookings/{bookingId}/cancel', [
    BookingController::class,
    'cancel',
])->whereNumber('bookingId');
