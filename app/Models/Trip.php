<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TripType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory;

    /**
     * The attributes that can be mass assigned.
     *
     * These fields are allowed when creating
     * or updating a trip using Eloquent.
     */
    protected $fillable = [
        'origin_city_id',
        'destination_city_id',
        'departure_at',
        'base_price',
        'type',
    ];

    /**
     * Cast database values to useful PHP types.
     *
     * departure_at:
     * Converts the database datetime value
     * to a Carbon datetime object.
     *
     * base_price:
     * Keeps the price formatted with two decimal places.
     *
     * type:
     * Converts the database value such as
     * "regular" or "vip" into the TripType Enum.
     */
    protected function casts(): array
    {
        return [
            'departure_at' => 'datetime',
            'base_price' => 'decimal:2',
            'type' => TripType::class,
        ];
    }

    /**
     * Get the city where this trip starts.
     *
     * Example:
     * Damascus -> Latakia
     *
     * Damascus is the origin city.
     */
    public function originCity(): BelongsTo
    {
        return $this->belongsTo(
            City::class,
            'origin_city_id'
        );
    }

    /**
     * Get the city where this trip ends.
     *
     * Example:
     * Damascus -> Latakia
     *
     * Latakia is the destination city.
     */
    public function destinationCity(): BelongsTo
    {
        return $this->belongsTo(
            City::class,
            'destination_city_id'
        );
    }

    /**
     * Get all seats that belong to this trip.
     *
     * One trip can have many seats.
     */
    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }
}
