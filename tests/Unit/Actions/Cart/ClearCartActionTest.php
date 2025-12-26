<?php

use App\Actions\Cart\ClearCartAction;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('ClearCartAction', function () {
    it('clears all items from cart', function () {
        $action = app(ClearCartAction::class);
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create(['stock_quantity' => 10]);
        foreach ($products as $product) {
            Cart::factory()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        $action->execute($user);

        expect(Cart::where('user_id', $user->id)->count())->toBe(0);
    });

    it('permanently deletes cart items', function () {
        $action = app(ClearCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $action->execute($user);

        // Should be permanently deleted, not just soft deleted
        expect(Cart::withTrashed()->where('user_id', $user->id)->count())->toBe(0);
    });

    it('only clears cart for the specified user', function () {
        $action = app(ClearCartAction::class);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        
        Cart::factory()->create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
        ]);
        Cart::factory()->create([
            'user_id' => $user2->id,
            'product_id' => $product->id,
        ]);

        $action->execute($user1);

        expect(Cart::where('user_id', $user1->id)->count())->toBe(0);
        expect(Cart::where('user_id', $user2->id)->count())->toBe(1);
    });

    it('handles empty cart gracefully', function () {
        $action = app(ClearCartAction::class);
        $user = User::factory()->create();

        // Should not throw exception
        $action->execute($user);

        expect(Cart::where('user_id', $user->id)->count())->toBe(0);
    });
});

