<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\FilterRequest;
use App\Http\Requests\Api\Order\StoreRequest;
use App\Http\Requests\Api\Order\UpdateStatusRequest;
use App\Models\Order;
use App\Services\Order\OrderService;

class OrderController extends Controller
{
    public function __construct(public OrderService $orderService)
    {
    }

    public function index(FilterRequest $request){
        return $this->orderService->index($request);
    }

    public function store(StoreRequest $request)
    {
        return $this->orderService->store($request);
    }

    public function show(string $orderNumber)
    {
        return $this->orderService->show($orderNumber);
    }

    public function updateOrderStatus(UpdateStatusRequest $request)
    {
        return $this->orderService->updateOrderStatus($request);
    }

    public function updateOrderStatusFromExternal(string $orderNumber)
    {
        return $this->orderService->updateOrderStatusFromExternal($orderNumber);
    }

}
