<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Trips that start from this city.
     */
    public function originTrips(): HasMany
    {
        return $this->hasMany(
            Trip::class,
            'origin_city_id'
        );
    }

    /**
     * Trips that end in this city.
     */
    public function destinationTrips(): HasMany
    {
        return $this->hasMany(
            Trip::class,
            'destination_city_id'
        );
    }
}
