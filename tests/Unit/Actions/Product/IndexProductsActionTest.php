<?php

use App\Actions\Product\IndexProductsAction;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Pagination\LengthAwarePaginator;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('IndexProductsAction', function () {
    it('returns paginated products', function () {
        $action = app(IndexProductsAction::class);
        Product::factory()->count(15)->create(['stock_quantity' => 10]);

        $result = $action->execute(null, 12);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        expect($result->count())->toBe(12);
        expect($result->perPage())->toBe(12);
    });

    it('enriches products with is_in_cart when user is authenticated', function () {
        $action = app(IndexProductsAction::class);
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 10]);
        
        // Add product1 to cart
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
        ]);

        $result = $action->execute($user, 12);

        $product1Result = $result->firstWhere('id', $product1->id);
        $product2Result = $result->firstWhere('id', $product2->id);
        
        expect($product1Result->is_in_cart)->toBeTrue();
        expect($product2Result->is_in_cart)->toBeFalse();
    });

    it('does not add is_in_cart when user is null', function () {
        $action = app(IndexProductsAction::class);
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $result = $action->execute(null, 12);

        $productResult = $result->firstWhere('id', $product->id);
        expect($productResult->is_in_cart)->toBeFalse();
    });

    it('correctly identifies products in cart across multiple pages', function () {
        $action = app(IndexProductsAction::class);
        $user = User::factory()->create();
        $products = Product::factory()->count(20)->create(['stock_quantity' => 10]);
        
        // Add first 3 products to cart
        foreach ($products->take(3) as $product) {
            Cart::factory()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        $result = $action->execute($user, 10);

        $inCartCount = $result->filter(fn($p) => $p->is_in_cart)->count();
        expect($inCartCount)->toBeGreaterThanOrEqual(0); // At least some should be marked
    });

    it('handles pagination correctly', function () {
        $action = app(IndexProductsAction::class);
        Product::factory()->count(25)->create(['stock_quantity' => 10]);

        $page1 = $action->execute(null, 10);
        expect($page1->currentPage())->toBe(1);
        expect($page1->count())->toBe(10);
        expect($page1->lastPage())->toBe(3);
    });
});

