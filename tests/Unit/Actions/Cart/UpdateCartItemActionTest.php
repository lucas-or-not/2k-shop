<?php

use App\Actions\Cart\UpdateCartItemAction;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Exceptions\CartException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\InsufficientStockException;
use App\DTOs\UpdateCartItemDto;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('UpdateCartItemAction', function () {
    it('updates cart item quantity', function () {
        $action = app(UpdateCartItemAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $dto = new UpdateCartItemDto(productId: $product->id, quantity: 3);

        $cart = $action->execute($user, $dto);

        expect($cart->quantity)->toBe(3);
    });

    it('throws exception for non-existent product', function () {
        $action = app(UpdateCartItemAction::class);
        $user = User::factory()->create();
        $dto = new UpdateCartItemDto(productId: 999, quantity: 1);

        expect(fn () => $action->execute($user, $dto))
            ->toThrow(CartException::class, 'Product not found.');
    });

    it('throws exception for out of stock product', function () {
        $action = app(UpdateCartItemAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 0]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
        $dto = new UpdateCartItemDto(productId: $product->id, quantity: 1);

        expect(fn () => $action->execute($user, $dto))
            ->toThrow(OutOfStockException::class);
    });

    it('throws exception when quantity exceeds available stock', function () {
        $action = app(UpdateCartItemAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 2]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $dto = new UpdateCartItemDto(productId: $product->id, quantity: 5);

        expect(fn () => $action->execute($user, $dto))
            ->toThrow(InsufficientStockException::class);
    });

    it('throws exception when product is not in cart', function () {
        $action = app(UpdateCartItemAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $dto = new UpdateCartItemDto(productId: $product->id, quantity: 2);

        expect(fn () => $action->execute($user, $dto))
            ->toThrow(CartException::class, 'Product is not in your cart.');
    });

    it('allows updating to maximum available stock', function () {
        $action = app(UpdateCartItemAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 5]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $dto = new UpdateCartItemDto(productId: $product->id, quantity: 5);

        $cart = $action->execute($user, $dto);

        expect($cart->quantity)->toBe(5);
    });

    it('loads product relationship', function () {
        $action = app(UpdateCartItemAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $dto = new UpdateCartItemDto(productId: $product->id, quantity: 2);

        $cart = $action->execute($user, $dto);

        expect($cart->relationLoaded('product'))->toBeTrue();
        expect($cart->product->id)->toBe($product->id);
    });
});

