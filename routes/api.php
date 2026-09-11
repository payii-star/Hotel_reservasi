<?php

use App\Http\Controllers\Api\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Api\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Api\Admin\HotelInfoController as AdminHotelInfoController;
use App\Http\Controllers\Api\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\HotelInfoController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (tamu / pengunjung web, tidak perlu login)
|--------------------------------------------------------------------------
*/
Route::get('/rooms', [RoomController::class, 'index']);
Route::get('/rooms/{room}', [RoomController::class, 'show']);

Route::get('/gallery', [GalleryController::class, 'index']);
Route::get('/hotel-info', [HotelInfoController::class, 'show']);

Route::get('/bookings/{code}', [BookingController::class, 'show']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES (butuh login, tamu maupun admin)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Booking kamar wajib login
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);

    /*
    |----------------------------------------------------------------------
    | ADMIN ONLY ROUTES
    |----------------------------------------------------------------------
    */
    Route::middleware('is_admin')->prefix('admin')->group(function () {
        // Kelola kamar
        Route::get('/rooms', [AdminRoomController::class, 'index']);
        Route::post('/rooms', [AdminRoomController::class, 'store']);
        Route::put('/rooms/{room}', [AdminRoomController::class, 'update']);
        Route::delete('/rooms/{room}', [AdminRoomController::class, 'destroy']);
        Route::post('/rooms/{room}/images', [AdminRoomController::class, 'addImage']);

        // Kelola booking
        Route::get('/bookings', [AdminBookingController::class, 'index']);
        Route::get('/bookings/{booking}', [AdminBookingController::class, 'show']);
        Route::patch('/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus']);

        // Kelola gallery
        Route::post('/gallery', [AdminGalleryController::class, 'store']);
        Route::put('/gallery/{gallery}', [AdminGalleryController::class, 'update']);
        Route::delete('/gallery/{gallery}', [AdminGalleryController::class, 'destroy']);

        // Kelola info hotel
        Route::put('/hotel-info', [AdminHotelInfoController::class, 'update']);
    });
});