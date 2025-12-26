<?php

use App\Actions\Cart\AddToCartAction;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Exceptions\CartException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\InsufficientStockException;
use App\DTOs\CartDto;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('AddToCartAction', function () {
    it('adds product to cart', function () {
        $action = app(AddToCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $dto = new CartDto(productId: $product->id, quantity: 2);

        $cart = $action->execute($user, $dto);

        expect($cart->user_id)->toBe($user->id);
        expect($cart->product_id)->toBe($product->id);
        expect($cart->quantity)->toBe(2);
        expect(Cart::where('user_id', $user->id)->where('product_id', $product->id)->exists())->toBeTrue();
    });

    it('throws exception for non-existent product', function () {
        $action = app(AddToCartAction::class);
        $user = User::factory()->create();
        $dto = new CartDto(productId: 999, quantity: 1);

        expect(fn () => $action->execute($user, $dto))
            ->toThrow(CartException::class, 'Product not found.');
    });

    it('throws exception for out of stock product', function () {
        $action = app(AddToCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 0]);
        $dto = new CartDto(productId: $product->id, quantity: 1);

        expect(fn () => $action->execute($user, $dto))
            ->toThrow(OutOfStockException::class);
    });

    it('throws exception when quantity exceeds available stock', function () {
        $action = app(AddToCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 2]);
        $dto = new CartDto(productId: $product->id, quantity: 5);

        expect(fn () => $action->execute($user, $dto))
            ->toThrow(InsufficientStockException::class);
    });

    it('adds to existing cart item quantity', function () {
        $action = app(AddToCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $dto = new CartDto(productId: $product->id, quantity: 3);

        $cart = $action->execute($user, $dto);

        expect($cart->quantity)->toBe(5); // 2 + 3
    });

    it('throws exception when adding to existing item would exceed stock', function () {
        $action = app(AddToCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 5]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
        $dto = new CartDto(productId: $product->id, quantity: 3); // 3 + 3 = 6, but only 5 available

        expect(fn () => $action->execute($user, $dto))
            ->toThrow(InsufficientStockException::class);
    });

    it('restores and updates soft-deleted cart item', function () {
        $action = app(AddToCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cartItem = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $cartItem->delete(); // Soft delete
        $dto = new CartDto(productId: $product->id, quantity: 2);

        $cart = $action->execute($user, $dto);

        expect($cart->trashed())->toBeFalse();
        expect($cart->quantity)->toBe(2); // Should be set to new quantity, not added
    });

    it('throws exception when restoring soft-deleted item would exceed stock', function () {
        $action = app(AddToCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 2]);
        $cartItem = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $cartItem->delete(); // Soft delete
        $dto = new CartDto(productId: $product->id, quantity: 5); // More than available

        expect(fn () => $action->execute($user, $dto))
            ->toThrow(InsufficientStockException::class);
    });
});

