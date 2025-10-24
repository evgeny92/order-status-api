<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\StoreRequest;
use App\Services\Order\OrderService;

class OrderController extends Controller
{
    public function __construct(public OrderService $orderService) {}

    public function store(StoreRequest $request)
    {
        return $this->orderService->store($request);
    }
}
