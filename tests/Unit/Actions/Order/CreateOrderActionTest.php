<?php

use App\Actions\Order\CreateOrderAction;
use App\Exceptions\CartException;
use App\Exceptions\InsufficientStockException;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('CreateOrderAction', function () {
    it('creates order from cart items', function () {
        $action = app(CreateOrderAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 100.00,
            'stock_quantity' => 10,
        ]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $order = $action->execute($user);

        expect($order)->toBeInstanceOf(Order::class);
        expect($order->user_id)->toBe($user->id);
        expect($order->status)->toBe('completed');
        expect($order->total_amount)->toBe(200.00);
    });

    it('calculates correct total amount for multiple items', function () {
        $action = app(CreateOrderAction::class);
        $user = User::factory()->create();
        $product1 = Product::factory()->create([
            'price' => 100.00,
            'stock_quantity' => 10,
        ]);
        $product2 = Product::factory()->create([
            'price' => 50.00,
            'stock_quantity' => 10,
        ]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 2, // 2 * 100 = 200
        ]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'quantity' => 1, // 1 * 50 = 50
        ]);

        $order = $action->execute($user);

        expect($order->total_amount)->toBe(250.00); // 200 + 50
    });

    it('creates order items with correct data', function () {
        $action = app(CreateOrderAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 100.00,
            'stock_quantity' => 10,
        ]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $order = $action->execute($user);

        $orderItem = OrderItem::where('order_id', $order->id)->first();
        expect($orderItem)->not->toBeNull();
        expect($orderItem->product_id)->toBe($product->id);
        expect($orderItem->quantity)->toBe(3);
        expect((float) $orderItem->price_at_purchase)->toBe(100.00);
    });

    it('decrements product stock', function () {
        $action = app(CreateOrderAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 10,
        ]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $action->execute($user);

        $product->refresh();
        expect($product->stock_quantity)->toBe(7); // 10 - 3
    });

    it('clears cart after order creation', function () {
        $action = app(CreateOrderAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $action->execute($user);

        expect(Cart::where('user_id', $user->id)->count())->toBe(0);
    });

    it('throws exception for empty cart', function () {
        $action = app(CreateOrderAction::class);
        $user = User::factory()->create();

        expect(fn () => $action->execute($user))
            ->toThrow(CartException::class, 'Cart is empty.');
    });

    it('throws exception for insufficient stock', function () {
        $action = app(CreateOrderAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 2,
        ]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5, // More than available
        ]);

        expect(fn () => $action->execute($user))
            ->toThrow(InsufficientStockException::class);
    });

    it('loads order with relationships', function () {
        $action = app(CreateOrderAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $order = $action->execute($user);

        expect($order->relationLoaded('orderItems'))->toBeTrue();
        expect($order->orderItems->first()->relationLoaded('product'))->toBeTrue();
    });

    it('handles multiple products with different quantities', function () {
        $action = app(CreateOrderAction::class);
        $user = User::factory()->create();
        $product1 = Product::factory()->create([
            'price' => 100.00,
            'stock_quantity' => 10,
        ]);
        $product2 = Product::factory()->create([
            'price' => 50.00,
            'stock_quantity' => 5,
        ]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);

        $order = $action->execute($user);

        expect($order->total_amount)->toBe(350.00); // (2*100) + (3*50)
        expect(OrderItem::where('order_id', $order->id)->count())->toBe(2);
        
        $product1->refresh();
        $product2->refresh();
        expect($product1->stock_quantity)->toBe(8); // 10 - 2
        expect($product2->stock_quantity)->toBe(2); // 5 - 3
    });
});

