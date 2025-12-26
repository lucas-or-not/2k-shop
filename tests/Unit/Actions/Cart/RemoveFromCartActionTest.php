<?php

use App\Actions\Cart\RemoveFromCartAction;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('RemoveFromCartAction', function () {
    it('removes product from cart', function () {
        $action = app(RemoveFromCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $action->execute($user, $product->id);

        expect(Cart::where('user_id', $user->id)->where('product_id', $product->id)->exists())->toBeFalse();
    });

    it('soft deletes cart item', function () {
        $action = app(RemoveFromCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $action->execute($user, $product->id);

        $cartItem->refresh();
        expect($cartItem->trashed())->toBeTrue();
    });

    it('only removes cart item for the specified user', function () {
        $action = app(RemoveFromCartAction::class);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        
        $cartItem1 = Cart::factory()->create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
        ]);
        $cartItem2 = Cart::factory()->create([
            'user_id' => $user2->id,
            'product_id' => $product->id,
        ]);

        $action->execute($user1, $product->id);

        expect($cartItem1->fresh()->trashed())->toBeTrue();
        expect($cartItem2->fresh()->trashed())->toBeFalse();
    });

    it('throws exception when removing non-existent cart item', function () {
        $action = app(RemoveFromCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        expect(fn () => $action->execute($user, $product->id))
            ->toThrow(\App\Exceptions\CartException::class, 'Product is not in your cart.');
    });
});

