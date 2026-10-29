<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'role',
        'university_id',
        'last_app_opened_at'
    ];

    // A user can have many listings
    public function listings()
    {
        return $this->hasMany(Listing::class);
    }
}
