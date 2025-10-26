<?php

namespace App\Services\Order;

use App\Enum\Order\OrderStatus;
use App\Events\OrderUpdated;
use App\Http\Resources\Order\OrderCreatedResource;
use App\Http\Resources\Order\OrdersAllResource;
use App\Http\Resources\Order\OrderShowResource;
use App\Http\Resources\Order\OrderUpdatedResource;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class OrderService
{
    public function index($request): JsonResponse
    {
        $orderFilterStatus = $request->status;
        $orderFilterTags = $request->tags;

        $orders = Order::query()
            ->when($orderFilterStatus, function ($query) use ($orderFilterStatus) {
                return $query->where('status', $orderFilterStatus);
            })
            ->when($orderFilterTags, function ($query) use ($orderFilterTags) {
                return $query->whereHas('tags', function ($query) use ($orderFilterTags) {
                    $query->whereIn('name', $orderFilterTags);
                    //$query->whereIn('slug', $orderFilterTags);
                });
            })
            ->with('tags')
            ->orderByDesc('created_at')
            ->get();

        if ($orders->isNotEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'Orders retrieved successfully.',
                'data' => OrdersAllResource::collection($orders),
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'The order list is empty',
        ]);
    }

    public function store($request): JsonResponse
    {
        try {

            DB::beginTransaction();

            $order = new Order();
            $order->order_number = $request->order_number;
            $order->total_amount = $request->total_amount;
            $order->status = OrderStatus::Pending;
            $order->save();

            $orderTags = $request->tags;

            if (!empty($orderTags) && count($orderTags)) {
                //Prepare tags from Trait
                $order->syncTags($orderTags);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order created successfully',
                'data' => new OrderCreatedResource($order->load('tags')),
            ]);

        } catch (\Exception $exception) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while creating the order.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ]);
        }
    }

    public function show($orderNumber): JsonResponse
    {
        $order = Order::query()->where('order_number', $orderNumber)
            ->with('tags')
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order details retrieved successfully.',
            'data' => new OrderShowResource($order),
        ]);
    }

    public function updateOrderStatus($request)
    {
        $orderId = $request->id;
        $orderStatus = $request->status;
        $orderTags = $request->tags;

        $order = Order::query()->where('id', $orderId)->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ]);
        }

        try {

            DB::beginTransaction();

            $changed = false;

            if ($orderStatus && $order->status->value !== $orderStatus) {
                $order->status = OrderStatus::from($orderStatus)->value;
                $order->save();
                $changed = true;
            }

            if (!empty($orderTags) && count($orderTags)) {
                //Prepare tags from Trait
                $order->syncTags($orderTags);
                $order->load('tags');
                $changed = true;
            }

            // If was changes, call the event
            if ($changed) {
                event(new OrderUpdated($order));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order updated successfully',
                'data' => new OrderUpdatedResource($order)
            ]);

        } catch (\Exception $exception) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while updating the order.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ]);
        }
    }

}
