<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Runs after successful email/password login AND after Auth::login on registration.
        Event::listen(Login::class, function (Login $event): void {
            $user = $event->user;

            if (!($user instanceof User) || $user->role !== 'student') {
                return;
            }

            $user->forceFill(['last_app_opened_at' => now()])->save();

            // Renew only listings withdrawn by inactivity, never manually hidden rooms.
            $user->listings()
                ->where('withdrawn_reason', 'inactivity')
                ->update([
                    'is_active' => true,
                    'withdrawn_reason' => null,
                ]);
        });
    }
}
