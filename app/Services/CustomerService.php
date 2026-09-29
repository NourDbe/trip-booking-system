<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;

final class CustomerService
{
    /**
     * Create a new customer.
     *
     * The controller does not interact directly
     * with the Eloquent model.
     */
    public function create(array $data): Customer
    {
        return Customer::query()->create([
            'name' => $data['name'],
            'phone' => $data['phone'],
        ]);
    }
}
