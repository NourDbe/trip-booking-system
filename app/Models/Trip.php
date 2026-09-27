<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    protected $fillable = [
        'trip_id',
        'seat_number',
    ];

    /**
     * Trip that owns this seat.
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Booking history for this seat.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
