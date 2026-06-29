<?php

use App\Http\Controllers\Api\BlogController as ApiBlogController;
use App\Http\Controllers\Api\GalleryController as ApiGalleryController;
use App\Http\Controllers\Api\RegistrationController as ApiRegistrationController;
use App\Http\Controllers\Api\SantriAuthController;
use App\Http\Controllers\Api\SantriDashboardController;
use App\Http\Controllers\Api\SantriHealthController;
use App\Http\Controllers\Api\SantriPermissionController;
use App\Http\Controllers\Api\SantriPrestasiController;
use App\Http\Controllers\Api\SantriProfileController;
use App\Http\Controllers\Api\SantriSecurityController;
use App\Http\Controllers\Api\SantriTarbiyahController;
use App\Http\Controllers\Api\SantriTopUpController as ApiSantriTopUpController;
use App\Http\Controllers\Api\SantriTransactionController;
use App\Http\Controllers\Api\SliderController as ApiSliderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public blog routes
Route::get('/blog', [ApiBlogController::class, 'index']);
Route::get('/blog/{slug}', [ApiBlogController::class, 'show']);

// Public gallery routes
Route::get('/gallery', [ApiGalleryController::class, 'index']);

// Public slider routes
Route::get('/slider', [ApiSliderController::class, 'index']);

// Public registration routes
Route::post('/registration', [ApiRegistrationController::class, 'store']);
Route::post('/contact', [ApiRegistrationController::class, 'contact']);

// Santri auth routes (public)
Route::post('/santri/login', [SantriAuthController::class, 'login']);

// Santri authenticated routes (Sanctum)
Route::middleware('auth:sanctum')->prefix('santri')->group(function () {
    // Auth
    Route::get('/me', [SantriAuthController::class, 'me']);
    Route::post('/logout', [SantriAuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard', [SantriDashboardController::class, 'index']);

    // Transactions
    Route::get('/transactions', [SantriTransactionController::class, 'index']);
    Route::get('/transactions/chart-data', [SantriTransactionController::class, 'chartData']);
    Route::get('/transactions/{transaction}', [SantriTransactionController::class, 'show']);
    Route::get('/riwayat', [SantriTransactionController::class, 'index']);
    Route::get('/riwayat/chart-data', [SantriTransactionController::class, 'chartData']);
    Route::get('/riwayat/{transaction}', [SantriTransactionController::class, 'show']);

    // Top-Up
    Route::get('/topups', [ApiSantriTopUpController::class, 'index']);
    Route::post('/topups', [ApiSantriTopUpController::class, 'store'])->middleware('santri.active');
    Route::get('/topups/pending-count', [ApiSantriTopUpController::class, 'pendingCount']);
    Route::get('/topups/{topUp}', [ApiSantriTopUpController::class, 'show']);

    // Profile
    Route::get('/profile', [SantriProfileController::class, 'index']);
    Route::post('/profile/change-pin', [SantriProfileController::class, 'changePin'])->middleware('santri.active');
    Route::post('/profile/email', [SantriProfileController::class, 'updateEmail'])->middleware('santri.active');
    Route::post('/profile/password', [SantriProfileController::class, 'updatePassword'])->middleware('santri.active');

    // Prestasi
    Route::get('/prestasi', [SantriPrestasiController::class, 'index']);
    Route::get('/prestasi/{prestasi}', [SantriPrestasiController::class, 'show']);

    // Perizinan
    Route::get('/permissions', [SantriPermissionController::class, 'index']);
    Route::get('/permissions/{permission}', [SantriPermissionController::class, 'show']);
    Route::get('/perizinan', [SantriPermissionController::class, 'index']);
    Route::get('/perizinan/{permission}', [SantriPermissionController::class, 'show']);

    // Keamanan
    Route::get('/security', [SantriSecurityController::class, 'index']);
    Route::get('/security/{violation}', [SantriSecurityController::class, 'show']);
    Route::get('/keamanan', [SantriSecurityController::class, 'index']);
    Route::get('/keamanan/{violation}', [SantriSecurityController::class, 'show']);

    // Tarbiyah
    Route::get('/tarbiyah', [SantriTarbiyahController::class, 'index']);

    // Kesehatan
    Route::get('/health', [SantriHealthController::class, 'index']);
    Route::get('/health/{health}', [SantriHealthController::class, 'show']);
    Route::get('/kesehatan', [SantriHealthController::class, 'index']);
    Route::get('/kesehatan/{health}', [SantriHealthController::class, 'show']);
});
