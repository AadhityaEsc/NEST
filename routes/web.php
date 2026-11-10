<?php

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
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


Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->role === 'landlord') {
        return Inertia::render('Dashboard', [
            'myListingsCount' => $user->listings()->count(),
            'activeListingsCount' => $user->listings()
                ->where('is_active', true)
                ->count(),
        ]);
    }

    return Inertia::render('Dashboard', [
        'savedListingsCount' => \App\Models\Wishlist::where(
            'user_id',
            $user->id
        )->count(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
