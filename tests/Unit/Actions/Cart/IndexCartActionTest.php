<?php

use App\Actions\Cart\IndexCartAction;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Pagination\LengthAwarePaginator;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('IndexCartAction', function () {
    it('returns paginated user cart', function () {
        $action = app(IndexCartAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $result = $action->execute($user, 15);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        expect($result->count())->toBe(1);
        expect($result->first()->product_id)->toBe($product->id);
        expect($result->first()->quantity)->toBe(2);
    });

    it('only returns cart items for the specified user', function () {
        $action = app(IndexCartAction::class);
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

        $result = $action->execute($user1, 15);

        expect($result->count())->toBe(1);
        expect($result->first()->user_id)->toBe($user1->id);
    });

    it('handles pagination correctly', function () {
        $action = app(IndexCartAction::class);
        $user = User::factory()->create();
        $products = Product::factory()->count(25)->create(['stock_quantity' => 10]);
        
        foreach ($products as $product) {
            Cart::factory()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        $page1 = $action->execute($user, 10);
        
        expect($page1->currentPage())->toBe(1);
        expect($page1->count())->toBe(10);
        expect($page1->lastPage())->toBe(3);
    });

    it('returns empty paginator when cart is empty', function () {
        $action = app(IndexCartAction::class);
        $user = User::factory()->create();

        $result = $action->execute($user, 15);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        expect($result->count())->toBe(0);
        expect($result->total())->toBe(0);
    });
});

