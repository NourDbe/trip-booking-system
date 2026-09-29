<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'origin' => [
                'id' => $this->originCity?->id,
                'name' => $this->originCity?->name,
            ],

            'destination' => [
                'id' => $this->destinationCity?->id,
                'name' => $this->destinationCity?->name,
            ],

            'departure_at' => $this->departure_at?->toISOString(),

            'base_price' => $this->base_price,

            'type' => $this->type->value,
        ];
    }
}
