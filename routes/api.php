<?php

use App\Http\Controllers\Api\BlogController as ApiBlogController;
use App\Http\Controllers\Api\GalleryController as ApiGalleryController;
use App\Http\Controllers\Api\SliderController as ApiSliderController;
use App\Http\Controllers\Api\RegistrationController as ApiRegistrationController;
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
