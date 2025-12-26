<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->product1 = Product::factory()->create([
        'name' => 'Product 1',
        'price' => 100.00,
        'stock_quantity' => 10,
    ]);
    $this->product2 = Product::factory()->create([
        'name' => 'Product 2',
        'price' => 50.00,
        'stock_quantity' => 5,
    ]);
});

describe('Order API', function () {
    it('requires authentication to create order', function () {
        $response = $this->postJson('/api/v1/orders');
        $response->assertStatus(401);
    });

    it('can create order from cart', function () {
        $this->actingAs($this->user);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
        ]);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product2->id,
            'quantity' => 1,
        ]);

        $response = $this->postJson('/api/v1/orders');

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'total_amount',
                    'status',
                    'order_items',
                ],
            ]);

        // Verify order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);

        // Verify order items were created
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product2->id,
            'quantity' => 1,
        ]);

        // Verify cart was cleared
        $this->assertDatabaseCount('carts', 0);

        // Verify stock was decremented
        $this->product1->refresh();
        $this->product2->refresh();
        expect($this->product1->stock_quantity)->toBe(8); // 10 - 2
        expect($this->product2->stock_quantity)->toBe(4); // 5 - 1
    });

    it('cannot create order with empty cart', function () {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/orders');

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Cart is empty.',
            ]);
    });

    it('cannot create order with insufficient stock', function () {
        $this->actingAs($this->user);
        $lowStockProduct = Product::factory()->create(['stock_quantity' => 1]);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $lowStockProduct->id,
            'quantity' => 5, // More than available
        ]);

        $response = $this->postJson('/api/v1/orders');

        $response->assertStatus(400);
    });

    it('can get user orders', function () {
        $this->actingAs($this->user);
        Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 250.00,
            'status' => 'completed',
        ]);

        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    });

    it('calculates correct total amount for order', function () {
        $this->actingAs($this->user);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 2, // 2 * 100 = 200
        ]);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product2->id,
            'quantity' => 1, // 1 * 50 = 50
        ]);

        $response = $this->postJson('/api/v1/orders');

        $response->assertStatus(201);
        $order = Order::where('user_id', $this->user->id)->first();
        expect($order->total_amount)->toBe(250.00); // 200 + 50
    });
});

