<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class OrderUpdateExternalFeatureTest extends TestCase
{
    protected string $orderNumber;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderNumber = 'ORD-' . fake()->unique()->numberBetween(1000, 9999);
    }

    public function test_updates_order_when_external_api_returns_success()
    {
        $order = Order::query()->create([
            'order_number' => $this->orderNumber,
            'status' => 'pending',
            'total_amount' => 350.50,
        ]);

        Http::fake([
            'https://external.integration/api/status/*' => Http::response([
                'status' => 'shipped',
            ], 200),
        ]);

        $response = $this->getJson('/api/v1/orders/' . $order->order_number . '/external');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Status updated from external successfully',
                'data' => [
                    'order_number' => $order->order_number,
                    'status' => 'shipped',
                    'updated_at' => $order->updated_at->toDateTimeString()
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'order_number' => $order->order_number,
            'status' => 'shipped',
        ]);
    }

    public function test_return_error_when_external_api_returns_http_error()
    {
        $order = Order::query()->create([
            'order_number' => $this->orderNumber,
            'status' => 'pending',
            'total_amount' => 350.50,
        ]);

        Http::fake([
            'https://external.integration/api/status/*' => Http::response(null, 500),
        ]);

         Log::shouldReceive('channel')
            ->with('orders_external')
            ->andReturnSelf()
            ->once();

        Log::shouldReceive('error')->once();

        $response = $this->getJson('/api/v1/orders/' . $order->order_number . '/external');

        $response->assertStatus(200)
        ->assertJson([
            'status' => false,
            'message' => 'External API error',
        ]);
    }

    public function test_logs_exception_when_external_api_throws_error()
    {
        $order = Order::query()->create([
            'order_number' => $this->orderNumber,
            'status' => 'pending',
            'total_amount' => 350.50,
        ]);

        Http::fake([
            'https://external.integration/api/status/*' => function () {
                throw new \Exception('Connection timeout');
            },
        ]);

        Log::shouldReceive('channel')
            ->with('orders_external')
            ->andReturnSelf()
            ->once();

        Log::shouldReceive('error')
            ->once();

        $response = $this->getJson('/api/v1/orders/' . $order->order_number . '/external');

        $response->assertStatus(200)
            ->assertJson([
                'status' => false,
                'message' => 'External API connection failed',
            ]);
    }
}
