<?php

use App\Actions\Product\ShowProductAction;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('ShowProductAction', function () {
    it('returns product by id', function () {
        $action = app(ShowProductAction::class);
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $result = $action->execute($product->id, null);

        expect($result)->not->toBeNull();
        expect($result->id)->toBe($product->id);
        expect($result->name)->toBe($product->name);
    });

    it('returns null for non-existent product', function () {
        $action = app(ShowProductAction::class);

        $result = $action->execute(999, null);

        expect($result)->toBeNull();
    });

    it('adds is_in_cart when user is authenticated and product is in cart', function () {
        $action = app(ShowProductAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $result = $action->execute($product->id, $user);

        expect($result->is_in_cart)->toBeTrue();
    });

    it('sets is_in_cart to false when user is authenticated but product is not in cart', function () {
        $action = app(ShowProductAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $result = $action->execute($product->id, $user);

        expect($result->is_in_cart)->toBeFalse();
    });

    it('does not add is_in_cart when user is null', function () {
        $action = app(ShowProductAction::class);
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $result = $action->execute($product->id, null);

        expect($result->is_in_cart ?? null)->toBeNull();
    });

    it('correctly checks if product is in user cart', function () {
        $action = app(ShowProductAction::class);
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 10]);
        
        // Only product1 in cart
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
        ]);

        $result1 = $action->execute($product1->id, $user);
        $result2 = $action->execute($product2->id, $user);

        expect($result1->is_in_cart)->toBeTrue();
        expect($result2->is_in_cart)->toBeFalse();
    });
});

