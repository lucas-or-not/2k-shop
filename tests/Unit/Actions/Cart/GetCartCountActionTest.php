<?php

use App\Actions\Cart\GetCartCountAction;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('GetCartCountAction', function () {
    it('returns correct cart count', function () {
        $action = app(GetCartCountAction::class);
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create(['stock_quantity' => 10]);
        foreach ($products as $product) {
            Cart::factory()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        $count = $action->execute($user);

        expect($count)->toBe(3);
    });

    it('returns zero for empty cart', function () {
        $action = app(GetCartCountAction::class);
        $user = User::factory()->create();

        $count = $action->execute($user);

        expect($count)->toBe(0);
    });

    it('excludes soft-deleted items from count', function () {
        $action = app(GetCartCountAction::class);
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 10]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
        ]);
        $cartItem2 = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
        ]);
        $cartItem2->delete(); // Soft delete

        $count = $action->execute($user);

        expect($count)->toBe(1);
    });

    it('only counts items for the specified user', function () {
        $action = app(GetCartCountAction::class);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $products = Product::factory()->count(5)->create(['stock_quantity' => 10]);
        
        foreach ($products->take(3) as $product) {
            Cart::factory()->create([
                'user_id' => $user1->id,
                'product_id' => $product->id,
            ]);
        }
        foreach ($products->skip(3) as $product) {
            Cart::factory()->create([
                'user_id' => $user2->id,
                'product_id' => $product->id,
            ]);
        }

        $count1 = $action->execute($user1);
        $count2 = $action->execute($user2);

        expect($count1)->toBe(3);
        expect($count2)->toBe(2);
    });
});

