<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\BookingStatus;

class Booking extends Model
{
    protected $fillable = [
        'customer_id',
        'seat_id',
        'status',
        'price',
    ];

    protected function casts(): array
    {
    return [
        'status' => BookingStatus::class,
        'price' => 'decimal:2',
    ];
    }

    /**
     * Customer who owns this booking.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Seat reserved by this booking.
     */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }
}
