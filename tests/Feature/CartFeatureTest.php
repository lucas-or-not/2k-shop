<?php

use App\Models\User;
use App\Models\Cart;
use App\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create(['stock_quantity' => 10]);
});

describe('Cart API', function () {
    it('requires authentication to access cart', function () {
        $response = $this->getJson('/api/v1/cart');
        $response->assertStatus(401);
    });

    it('can get user cart', function () {
        $this->actingAs($this->user);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
        $response = $this->getJson('/api/v1/cart');
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

    it('can add product to cart', function () {
        $this->actingAs($this->user);
        $response = $this->postJson('/api/v1/cart', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Product added to cart successfully',
            ])
            ->assertJsonStructure([
                'data',
            ]);
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);
    });

    it('can add product to cart with quantity', function () {
        $this->actingAs($this->user);
        $response = $this->postJson('/api/v1/cart', [
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);
    });

    it('cannot add non-existent product to cart', function () {
        $this->actingAs($this->user);
        $response = $this->postJson('/api/v1/cart', [
            'product_id' => 999,
            'quantity' => 1,
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    });

    it('cannot add out of stock product to cart', function () {
        $this->actingAs($this->user);
        $outOfStockProduct = Product::factory()->create(['stock_quantity' => 0]);
        $response = $this->postJson('/api/v1/cart', [
            'product_id' => $outOfStockProduct->id,
            'quantity' => 1,
        ]);
        $response->assertStatus(400);
    });

    it('cannot add more quantity than available stock', function () {
        $this->actingAs($this->user);
        $lowStockProduct = Product::factory()->create(['stock_quantity' => 2]);
        $response = $this->postJson('/api/v1/cart', [
            'product_id' => $lowStockProduct->id,
            'quantity' => 5,
        ]);
        $response->assertStatus(400);
    });

    it('can update cart item quantity', function () {
        $this->actingAs($this->user);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);
        $response = $this->putJson('/api/v1/cart', [
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Cart item updated successfully',
            ]);
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);
    });

    it('can remove product from cart', function () {
        $this->actingAs($this->user);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
        $response = $this->deleteJson('/api/v1/cart', [
            'product_id' => $this->product->id,
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product removed from cart successfully',
            ]);
        $this->assertDatabaseMissing('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'deleted_at' => null,
        ]);
    });

    it('can toggle product in cart - add', function () {
        $this->actingAs($this->user);
        $response = $this->postJson('/api/v1/cart/toggle', [
            'product_id' => $this->product->id,
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'action' => 'added',
                    'is_in_cart' => true,
                ],
            ]);
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    });

    it('can toggle product in cart - remove', function () {
        $this->actingAs($this->user);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
        $response = $this->postJson('/api/v1/cart/toggle', [
            'product_id' => $this->product->id,
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'action' => 'removed',
                    'is_in_cart' => false,
                ],
            ]);
    });

    it('can check if product is in cart', function () {
        $this->actingAs($this->user);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
        $response = $this->getJson("/api/v1/cart/check/{$this->product->id}");
        $response->assertStatus(200)
            ->assertJson([
                'is_in_cart' => true,
            ]);
    });

    it('can get cart count', function () {
        $this->actingAs($this->user);
        Cart::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);
        $response = $this->getJson('/api/v1/cart/count');
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'count' => 3,
                ],
            ]);
    });

    it('can clear cart', function () {
        $this->actingAs($this->user);
        Cart::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);
        $response = $this->deleteJson('/api/v1/cart/clear');
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Cart cleared successfully',
            ]);
        $this->assertDatabaseCount('carts', 0);
    });
});

