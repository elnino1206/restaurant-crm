<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Booking\Actions\CreateCustomerAction;
use App\Domains\Booking\Models\Customer;
use App\Domains\Booking\Requests\CreateCustomerRequest;
use App\Domains\Booking\Requests\UpdateCustomerRequest;
use App\Domains\Booking\Resources\CustomerResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Customer::class);

        $customers = Customer::cursorPaginate(15);

        return CustomerResource::collection($customers);
    }

    public function store(CreateCustomerRequest $request): JsonResponse
    {
        $this->authorize('create', Customer::class);

        $customer = app(CreateCustomerAction::class)->handle($request->toDTO());

        return CustomerResource::make($customer)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Customer $customer): JsonResponse
    {
        $this->authorize('view', $customer);

        return CustomerResource::make($customer)->response();
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $this->authorize('update', $customer);

        $data = $request->toDTO();

        $customer->update(array_filter([
            'name'        => $data->name,
            'phone'       => $data->phone,
            'email'       => $data->email,
            'preferences' => $data->preferences,
            'notes'       => $data->notes,
        ], fn ($v) => $v !== null));

        return CustomerResource::make($customer->fresh())->response();
    }
}
