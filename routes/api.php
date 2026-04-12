<?php

use App\Http\Controllers\Api\BlogController as ApiBlogController;
use App\Http\Controllers\Api\GalleryController as ApiGalleryController;
use App\Http\Controllers\Api\SliderController as ApiSliderController;
use App\Http\Controllers\Api\RegistrationController as ApiRegistrationController;
use App\Http\Controllers\Api\SantriAuthController;
use App\Http\Controllers\Api\SantriDashboardController;
use App\Http\Controllers\Api\SantriTransactionController;
use App\Http\Controllers\Api\SantriTopUpController as ApiSantriTopUpController;
use App\Http\Controllers\Api\SantriProfileController;
use App\Http\Controllers\Api\SantriPrestasiController;
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

    // Top-Up
    Route::get('/topups', [ApiSantriTopUpController::class, 'index']);
    Route::post('/topups', [ApiSantriTopUpController::class, 'store']);
    Route::get('/topups/pending-count', [ApiSantriTopUpController::class, 'pendingCount']);
    Route::get('/topups/{topUp}', [ApiSantriTopUpController::class, 'show']);

    // Profile
    Route::get('/profile', [SantriProfileController::class, 'index']);
    Route::post('/profile/change-pin', [SantriProfileController::class, 'changePin']);
    Route::post('/profile/email', [SantriProfileController::class, 'updateEmail']);
    Route::post('/profile/password', [SantriProfileController::class, 'updatePassword']);

    // Prestasi
    Route::get('/prestasi', [SantriPrestasiController::class, 'index']);
    Route::get('/prestasi/{prestasi}', [SantriPrestasiController::class, 'show']);
});
