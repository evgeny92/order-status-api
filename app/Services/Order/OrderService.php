<?php

namespace App\Services\Order;

use App\Http\Resources\Order\OrderCreatedResource;
use App\Models\Order;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function store($request)
    {
        try {

            DB::beginTransaction();

            $order = new Order();
            $order->order_number = $request->order_number;
            $order->total_amount = $request->total_amount;
            $order->statu = 'pending';
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
}
