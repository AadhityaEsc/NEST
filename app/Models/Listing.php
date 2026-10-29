<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'rent_amount',
        'latitude',
        'longitude',
        'is_active',
        'last_interaction_at',
        'has_wishlist_extension'
    ];

    // A listing belongs to a user (student or landlord)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A listing can be favorited by many users
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
