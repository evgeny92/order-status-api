<?php

namespace Tests\Feature;

use App\Events\OrderStatusChanged;
use App\Models\Order;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderUpdateFeatureTest extends TestCase
{
    public function test_order_updated_event_is_dispatched_when_status_and_tags_are_changed()
    {
        $orderNumber = 'ORD-' . fake()->unique()->numberBetween(1000, 9999);

        $order = Order::query()->create([
            'status' => 'pending',
            'order_number' => $orderNumber,
            'total_amount' => 550.50,
        ]);

        Event::fake();

        $orderDataForUpdate = [
            'id' => $order->id,
            'status' => 'shipped',
            'tags' => ['New1', 'New2'],
        ];

        $response = $this->postJson('/api/v1/orders/status', $orderDataForUpdate);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Order updated successfully',
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => 'shipped',
                    'tags' => ['New1', 'New2'],
                    'updated_at' => $order->updated_at->toDateTimeString()
                ],

            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
        ]);

        Event::assertDispatched(OrderStatusChanged::class, function ($event) use ($order) {
            return $event->order->id === $order->id &&
                $event->order->status->value === 'shipped';
        });
    }
}
