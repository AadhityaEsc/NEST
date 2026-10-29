<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MapController;

// Public registration route
Route::post('/register', [AuthController::class, 'register']);

// Protected routes (Requires Auth token)
Route::middleware('auth:sanctum')->group(function () {
    // Map bounding box query
    Route::get('/map/listings', [MapController::class, 'getListingsInBounds']);

    // Additional routes you will add later:
    // Route::post('/listings', [ListingController::class, 'store']);
    // Route::post('/wishlist/{listing}', [WishlistController::class, 'store']);
});
