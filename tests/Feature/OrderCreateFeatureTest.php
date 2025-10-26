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
            'total_amount' => 599.30,
            'tags' => ['New', 'Old'],
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
                    'created_at'
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'order_number' => $orderNumber,
            'total_amount' => 599.30,
        ]);

        $this->assertDatabaseHas('tags', ['name' => 'New']);
        $this->assertDatabaseHas('tags', ['name' => 'Old']);
    }

    public function test_order_creation_fails_with_empty_fields()
    {
        $payload = [
            'order_number' => '',
            'total_amount' => null,
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_number', 'total_amount']);
    }
}
