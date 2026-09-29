<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class CustomerController extends Controller
{
    use HasApiResponse;

    public function __construct(
        private CustomerService $customerService
    ) {
    }

    /**
     * Create a new customer.
     */
    public function store(
        StoreCustomerRequest $request
    ): JsonResponse {
        $customer = $this->customerService->create(
            $request->validated()
        );

        return $this->successResponse(
            (new CustomerResource($customer))->resolve($request),
            'Customer created successfully.',
            201
        );
    }
}
