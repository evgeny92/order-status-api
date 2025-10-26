<?php

namespace Tests\Feature;

//use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCreateFeatureTest extends TestCase
{
    //use RefreshDatabase;

    public function test_can_create_a_new_order(): void
    {

        $orderNumber = 'ORD-' . fake()->unique()->numberBetween(1000, 9999);

        $orderData = [
            'order_number' => $orderNumber,
            'tags' => ['New', 'Old'],
            'items' => [
                ['product_name' => 'Laptop', 'quantity' => 1, 'price' => 500.00],
                ['product_name' => 'Mouse', 'quantity' => 2, 'price' => 49.65],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'order_number',
                    'total_amount',
                    'status',
                    'tags' => [
                        ['name', 'slug', 'added_at']
                    ],
                    'items' => [
                        ['product_name', 'quantity', 'price']
                    ],
                    'created_at'
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'order_number' => $orderNumber,
        ]);

        $this->assertDatabaseHas('tags', ['name' => 'New']);
        $this->assertDatabaseHas('tags', ['name' => 'Old']);
        $this->assertDatabaseHas('order_items', ['product_name' => 'Laptop', 'quantity' => 1, 'price' => 500.00]);
        $this->assertDatabaseHas('order_items', ['product_name' => 'Mouse', 'quantity' => 2, 'price' => 49.65]);
    }

    public function test_order_creation_fails_with_empty_fields()
    {
        $payload = [
            'order_number' => '',
            'items' => [],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_number', 'items']);
    }
}
