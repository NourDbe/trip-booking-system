<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    use HasFactory;

    /**
     * Fields that can be mass assigned.
     */
    protected $fillable = [
        'trip_id',
        'seat_number',
    ];

    /**
     * The trip that this seat belongs to.
     *
     * One trip can have many seats,
     * but each seat belongs to one trip.
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Get all booking records for this seat.
     *
     * A seat can have multiple booking records over time
     * because an old booking may be cancelled
     * and the seat can then be booked again.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
