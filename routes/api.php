<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OfficeLocationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API Routes (v1)
|--------------------------------------------------------------------------
|
| Endpoint API yang dikonsumsi oleh aplikasi mobile smartphone pegawai
| untuk autentikasi, sinkronisasi lokasi kantor, dan presensi.
|
*/

Route::prefix('v1')->group(function () {
    // Public Auth Endpoint
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected Endpoints (Requires Sanctum Bearer Token)
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Master Data Lokasi Kantor & Geofence
        Route::get('/office-locations', [OfficeLocationController::class, 'index']);

        // Presensi Pegawai
        Route::prefix('attendance')->group(function () {
            Route::post('/check-in', [AttendanceController::class, 'checkIn']);
            Route::post('/check-out', [AttendanceController::class, 'checkOut']);
            Route::get('/today', [AttendanceController::class, 'today']);
            Route::get('/history', [AttendanceController::class, 'history']);
        });
    });
});
