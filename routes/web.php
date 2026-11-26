<?php

use App\Http\Controllers\ListingController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MapboxSearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\EnsureStudent;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified', EnsureStudent::class])->group(function () {
    Route::get('/dashboard', function () {
        $user = request()->user();
        return Inertia::render('Dashboard', [
            'myListingsCount' => $user->listings()->count(),
            'activeListingsCount' => $user->listings()->discoverable()->count(),
            'savedListingsCount' => $user->wishlists()->count(),
        ]);
    })->name('dashboard');

    Route::get('/listings', [ListingController::class, 'index'])->name('listings.index');
    Route::get('/listings/create', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/listings', [ListingController::class, 'store'])->name('listings.store');
    Route::get('/my-listings', [ListingController::class, 'mine'])->name('listings.mine');
    Route::patch('/my-listings/{listing}/withdraw', [ListingController::class, 'withdraw'])->name('listings.withdraw');
    Route::patch('/my-listings/{listing}/republish', [ListingController::class, 'republish'])->name('listings.republish');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{listing}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{listing}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // Browser uses session cookies; no Sanctum setup needed for this site.
    Route::get('/map', [MapController::class, 'index'])->name('map.index');
    Route::get('/map/listings', [MapController::class, 'getListingsInBounds'])->name('map.listings');
    Route::get('/map/place-search', MapboxSearchController::class)->middleware('throttle:10,1')->name('map.place-search');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
