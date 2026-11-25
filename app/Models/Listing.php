<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    protected $fillable = [
        'type',
        'title',
        'description',
        'rent_amount',
        'latitude',
        'longitude',
        'is_active',
        'withdrawn_reason',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'rent_amount' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * A listing is discoverable for 72 hours after the later of:
     * publishing it, or the publisher's most recent successful login.
     * This additionally hides old/legacy landlord listings.
     */
    public function scopeDiscoverable(Builder $query): Builder
    {
        $cutoff = now()->subHours(72);

        return $query
            ->where('is_active', true)
            ->whereHas('user', function (Builder $users) {
                $users->where('role', 'student')->whereNotNull('university_id');
            })
            ->where(function (Builder $recent) use ($cutoff) {
                $recent->where('created_at', '>=', $cutoff)
                    ->orWhereHas('user', function (Builder $users) use ($cutoff) {
                        $users->where('last_app_opened_at', '>=', $cutoff);
                    });
            });
    }
}
