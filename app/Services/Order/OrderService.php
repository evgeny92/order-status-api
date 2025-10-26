<?php

namespace App\Services\Order;

use App\Enum\Order\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Http\Resources\Order\OrderCreatedResource;
use App\Http\Resources\Order\OrdersAllResource;
use App\Http\Resources\Order\OrderShowResource;
use App\Http\Resources\Order\OrderUpdatedExternalResource;
use App\Http\Resources\Order\OrderUpdatedResource;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        //dd($request->all());

        try {

            DB::beginTransaction();

            $order = new Order();
            $order->order_number = $request->order_number;
            $order->status = OrderStatus::Pending;
            $order->save();

            $orderTags = $request->tags;

            if (!empty($orderTags) && count($orderTags)) {
                //Prepare tags from Trait
                $order->syncTags($orderTags);
            }

            $orderItems = $request->items;

            if (!empty($orderItems) && count($orderItems)) {
                $order->items()->createMany($orderItems);

                $total_amount = collect($orderItems)->sum(fn($item) => $item['quantity'] * $item['price']);

                $order->total_amount = $total_amount;
                $order->save();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order created successfully',
                'data' => new OrderCreatedResource($order->load(['tags', 'items'])),
            ], 201);

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
            ->with(['tags', 'items'])
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
                event(new OrderStatusChanged($order));
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

    public function updateOrderStatusFromExternal($orderNumber): JsonResponse
    {
        $order = Order::query()->where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ]);
        }

        try {
            $response = Http::get('https://external.integration/api/status/' . $order->order_number);

            if ($response->successful()) {
                $orderData = $response->json();

                $order->status = OrderStatus::from($orderData['status'])->value;
                $order->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Status updated from external successfully',
                    'data' => new OrderUpdatedExternalResource($order),
                ]);
            }

            Log::channel('orders_external')->error("API returned error: " . $response->status());

            return response()->json([
                'status' => false,
                'message' => 'External API error',
            ]);

        } catch (\Exception $exception) {

            Log::channel('orders_external')->error("API returned exception: " . $exception->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'External API connection failed',
            ]);
        }
    }

}
