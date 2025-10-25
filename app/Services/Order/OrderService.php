<?php

namespace App\Services\Order;

use App\Http\Resources\Order\OrderCreatedResource;
use App\Http\Resources\Order\OrdersAllResource;
use App\Http\Resources\Order\OrderShowResource;
use App\Models\Order;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
                    $query->whereIn('slug', $orderFilterTags);
                });
            })
            ->with('tags')
            ->orderByDesc('created_at')
            ->get();

        if($orders->isNotEmpty()) {
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
            $order->status = 'pending';
            $order->save();

            $tags = $request->tags;

            if (!empty($tags) && count($tags)) {

                $tagIds = [];
                foreach ($tags as $tag) {

                    $tagSlug = Str::slug($tag, '-');

                    $tagItem = Tag::query()->firstOrCreate(
                        ['slug' => $tagSlug],
                        ['name' => $tag]
                    );

                    $tagIds[$tagItem->id] = ['added_at' => now()];
                }

                $order->tags()->sync($tagIds);
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

}
