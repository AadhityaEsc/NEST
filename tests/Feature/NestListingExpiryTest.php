<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\University;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NestListingExpiryTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        $uni = University::create([
            'name' => 'Test University',
            'email_domain' => 'test.ac.uk',
        ]);

        return User::create([
            'name' => 'Student One',
            'email' => 'student@test.ac.uk',
            'password' => bcrypt('secret-password'),
            'role' => 'student',
            'university_id' => $uni->id,
            'last_app_opened_at' => now()->subDays(5),
        ]);
    }

    private function oldListing(User $user): Listing
    {
        $listing = $user->listings()->create([
            'type' => 'room_available',
            'title' => 'Room near university',
            'description' => 'A spare student room with shared kitchen and common area.',
            'rent_amount' => 400,
            'latitude' => 52.5878000,
            'longitude' => -2.1270000,
            'is_active' => true,
        ]);
        $listing->forceFill(['created_at' => now()->subDays(4)])->save();

        return $listing;
    }

    public function test_listing_expires_after_72_hours_without_a_login(): void
    {
        $listing = $this->oldListing($this->student());
        $this->artisan('listings:clean')->assertExitCode(0);

        $this->assertFalse($listing->fresh()->is_active);
        $this->assertSame('inactivity', $listing->fresh()->withdrawn_reason);
    }

    public function test_successful_login_restarts_clock_and_restores_expired_room(): void
    {
        $user = $this->student();
        $listing = $this->oldListing($user);
        $this->artisan('listings:clean')->assertExitCode(0);

        Event::dispatch(new Login('web', $user, false));
        $this->assertTrue($listing->fresh()->is_active);
        $this->assertNull($listing->fresh()->withdrawn_reason);
        $this->assertSame(1, Listing::discoverable()->count());
    }

    public function test_manual_withdrawal_does_not_republish_on_login(): void
    {
        $user = $this->student();
        $listing = $this->oldListing($user);
        $listing->update(['is_active' => false, 'withdrawn_reason' => 'manual']);

        Event::dispatch(new Login('web', $user, false));
        $this->assertFalse($listing->fresh()->is_active);
    }
}
