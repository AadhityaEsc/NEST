<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Listing;
use Carbon\Carbon;

class CleanExpiredListings extends Command
{
    protected $signature = 'listings:clean';
    protected $description = 'Withdraw listings if owner is inactive for 3 days, granting 1-time extension if wishlisted.';

    public function handle()
    {
        // Define the cutoff time (72 hours ago)
        $threeDaysAgo = Carbon::now()->subDays(3);

        // Fetch all currently active listings with their owners and wishlists
        $activeListings = Listing::where('is_active', true)
            ->with(['user', 'wishlists'])
            ->get();

        foreach ($activeListings as $listing) {
            $ownerLastActive = $listing->user->last_app_opened_at;

            // Note: If you implement a messages table, you would also check:
            // $hasRecentMessages = $listing->messages()->where('created_at', '>=', $threeDaysAgo)->exists();
            $hasRecentMessages = false;

            // Condition: Owner has not opened app in 3 days AND no new messages
            if ($ownerLastActive < $threeDaysAgo && !$hasRecentMessages) {

                // Does this listing have any saves/wishlists?
                $isWishlisted = $listing->wishlists->count() > 0;

                // If wishlisted AND hasn't used their extension yet
                if ($isWishlisted && !$listing->has_wishlist_extension) {
                    $listing->update([
                        'has_wishlist_extension' => true,
                        // Reset the interaction clock to give them 3 more days
                        'last_interaction_at' => Carbon::now()
                    ]);
                    $this->info("Listing {$listing->id} granted 3-day extension.");
                }
                // If no wishlists OR already used their extension
                else {
                    $listing->update(['is_active' => false]);
                    $this->info("Listing {$listing->id} withdrawn due to inactivity.");
                }
            }
        }
    }
}
