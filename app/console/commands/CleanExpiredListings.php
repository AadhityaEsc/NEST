<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class CleanExpiredListings extends Command
{
    protected $signature = 'listings:clean';
    protected $description = 'Withdraw student listings when publishers have been inactive for over 72 hours.';

    public function handle(): int
    {
        $cutoff = now()->subHours(72);

        // The posting date is the initial start time. Each login resets the clock.
        $withdrawn = Listing::query()
            ->where('is_active', true)
            ->where('created_at', '<', $cutoff)
            ->whereHas('user', function (Builder $users) use ($cutoff) {
                $users->where(function (Builder $inactive) use ($cutoff) {
                    $inactive->whereNull('last_app_opened_at')
                        ->orWhere('last_app_opened_at', '<', $cutoff);
                });
            })
            ->update([
                'is_active' => false,
                'withdrawn_reason' => 'inactivity',
            ]);

        $this->info("Withdrawn {$withdrawn} expired listings.");

        return self::SUCCESS;
    }
}
