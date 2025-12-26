<?php

use App\Actions\Cart\ToggleCartAction;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('ToggleCartAction', function () {
    it('adds product to cart when not in cart', function () {
        $action = app(ToggleCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $result = $action->execute($user, $product->id);

        expect($result['action'])->toBe('added');
        expect($result['is_in_cart'])->toBeTrue();
        expect($result['count'])->toBe(1);
        expect(Cart::where('user_id', $user->id)->where('product_id', $product->id)->exists())->toBeTrue();
    });

    it('removes product from cart when already in cart', function () {
        $action = app(ToggleCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $result = $action->execute($user, $product->id);

        expect($result['action'])->toBe('removed');
        expect($result['is_in_cart'])->toBeFalse();
        expect($result['count'])->toBe(0);
    });

    it('updates cart count correctly when adding', function () {
        $action = app(ToggleCartAction::class);
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 10]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
        ]);

        $result = $action->execute($user, $product2->id);

        expect($result['count'])->toBe(2);
    });

    it('updates cart count correctly when removing', function () {
        $action = app(ToggleCartAction::class);
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 10]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
        ]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
        ]);

        $result = $action->execute($user, $product1->id);

        expect($result['count'])->toBe(1);
    });
});

