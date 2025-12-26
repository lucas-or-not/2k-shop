<?php

use App\Actions\Order\IndexOrdersAction;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('IndexOrdersAction', function () {
    it('returns paginated user orders', function () {
        $action = app(IndexOrdersAction::class);
        $user = User::factory()->create();
        Order::factory()->count(5)->create([
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $result = $action->execute($user, 15);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        expect($result->count())->toBe(5);
    });

    it('only returns orders for the specified user', function () {
        $action = app(IndexOrdersAction::class);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Order::factory()->count(3)->create([
            'user_id' => $user1->id,
            'status' => 'completed',
        ]);
        Order::factory()->count(2)->create([
            'user_id' => $user2->id,
            'status' => 'completed',
        ]);

        $result = $action->execute($user1, 15);

        expect($result->count())->toBe(3);
        $result->each(fn($order) => expect($order->user_id)->toBe($user1->id));
    });

    it('orders by created_at desc', function () {
        $action = app(IndexOrdersAction::class);
        $user = User::factory()->create();
        
        $oldOrder = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'created_at' => now()->subDays(2),
        ]);
        $newOrder = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'created_at' => now(),
        ]);

        $result = $action->execute($user, 15);

        expect($result->first()->id)->toBe($newOrder->id);
        expect($result->last()->id)->toBe($oldOrder->id);
    });

    it('handles pagination correctly', function () {
        $action = app(IndexOrdersAction::class);
        $user = User::factory()->create();
        Order::factory()->count(25)->create([
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $page1 = $action->execute($user, 10);
        
        expect($page1->currentPage())->toBe(1);
        expect($page1->count())->toBe(10);
        expect($page1->lastPage())->toBe(3);
    });

    it('eager loads order items and products', function () {
        $action = app(IndexOrdersAction::class);
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        $result = $action->execute($user, 15);

        $orderResult = $result->first();
        expect($orderResult->relationLoaded('orderItems'))->toBeTrue();
        expect($orderResult->orderItems->first()->relationLoaded('product'))->toBeTrue();
    });

    it('returns empty paginator when user has no orders', function () {
        $action = app(IndexOrdersAction::class);
        $user = User::factory()->create();

        $result = $action->execute($user, 15);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
        expect($result->count())->toBe(0);
        expect($result->total())->toBe(0);
    });
});

