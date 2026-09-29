<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'status' => $this->status->value,

            'price' => $this->price,

            'customer' => [
                'id' => $this->customer?->id,
                'name' => $this->customer?->name,
                'phone' => $this->customer?->phone,
            ],

            'seat' => [
                'id' => $this->seat?->id,
                'number' => $this->seat?->seat_number,
            ],

            'trip' => [
                'id' => $this->seat?->trip?->id,

                'origin' =>
                    $this->seat?->trip?->originCity?->name,

                'destination' =>
                    $this->seat?->trip?->destinationCity?->name,

                'departure_at' =>
                    $this->seat?->trip?->departure_at?->toISOString(),
            ],

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
