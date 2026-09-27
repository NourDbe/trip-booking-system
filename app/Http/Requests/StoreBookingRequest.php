<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Allow API clients to create bookings.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a booking.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'seat_id' => [
                'required',
                'integer',
                'exists:seats,id',
            ],
        ];
    }
}
