<?php

use App\Actions\Cart\CheckCartAction;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('CheckCartAction', function () {
    it('returns true when product is in cart', function () {
        $action = app(CheckCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $result = $action->execute($user, $product->id);

        expect($result)->toBeTrue();
    });

    it('returns false when product is not in cart', function () {
        $action = app(CheckCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $result = $action->execute($user, $product->id);

        expect($result)->toBeFalse();
    });

    it('returns false for soft-deleted cart items', function () {
        $action = app(CheckCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
        $cartItem->delete(); // Soft delete

        $result = $action->execute($user, $product->id);

        expect($result)->toBeFalse();
    });

    it('only checks cart for the specified user', function () {
        $action = app(CheckCartAction::class);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        
        Cart::factory()->create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
        ]);

        $result1 = $action->execute($user1, $product->id);
        $result2 = $action->execute($user2, $product->id);

        expect($result1)->toBeTrue();
        expect($result2)->toBeFalse();
    });
});

