<?php

use App\Jobs\DailySalesReportJob;
use App\Mail\DailySalesReport;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('DailySalesReportJob', function () {
    it('sends daily sales report to admin', function () {
        Mail::fake();
        
        $admin = User::factory()->create([
            'email' => config('mail.admin_email', 'admin@2kshop.com'),
        ]);
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['price' => 100.00]);
        $product2 = Product::factory()->create(['price' => 50.00]);

        // Create orders for yesterday
        $order1 = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 200.00,
            'status' => 'completed',
            'created_at' => Carbon::yesterday(),
        ]);
        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price_at_purchase' => 100.00,
        ]);

        $order2 = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 50.00,
            'status' => 'completed',
            'created_at' => Carbon::yesterday(),
        ]);
        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price_at_purchase' => 50.00,
        ]);

        $job = new DailySalesReportJob();
        $orderRepository = app(\App\Repositories\OrderRepository::class);
        $job->handle($orderRepository);

        Mail::assertQueued(DailySalesReport::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin->email);
        });
    });

    it('does not send email if admin user does not exist', function () {
        Mail::fake();
        
        $job = new DailySalesReportJob();
        $orderRepository = app(\App\Repositories\OrderRepository::class);
        $job->handle($orderRepository);

        Mail::assertNothingQueued();
    });

    it('only includes completed orders from yesterday', function () {
        Mail::fake();
        
        $admin = User::factory()->create([
            'email' => config('mail.admin_email', 'admin@2kshop.com'),
        ]);
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100.00]);

        // Yesterday's completed order (should be included)
        $yesterdayOrder = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 200.00,
            'status' => 'completed',
            'created_at' => Carbon::yesterday(),
        ]);

        // Today's order (should not be included)
        $todayOrder = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 100.00,
            'status' => 'completed',
            'created_at' => Carbon::today(),
        ]);

        // Pending order (should not be included)
        $pendingOrder = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 300.00,
            'status' => 'pending',
            'created_at' => Carbon::yesterday(),
        ]);

        $job = new DailySalesReportJob();
        $orderRepository = app(\App\Repositories\OrderRepository::class);
        $job->handle($orderRepository);

        Mail::assertQueued(DailySalesReport::class, function ($mail) {
            $summary = $mail->salesSummary;
            // Should only include yesterday's completed order
            return $summary['total_orders'] === 1
                && $summary['total_revenue'] === 200.00;
        });
    });

    it('calculates correct sales summary', function () {
        Mail::fake();
        
        $admin = User::factory()->create([
            'email' => config('mail.admin_email', 'admin@2kshop.com'),
        ]);
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['name' => 'Product 1', 'price' => 100.00]);
        $product2 = Product::factory()->create(['name' => 'Product 2', 'price' => 50.00]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 250.00,
            'status' => 'completed',
            'created_at' => Carbon::yesterday(),
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price_at_purchase' => 100.00,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price_at_purchase' => 50.00,
        ]);

        $job = new DailySalesReportJob();
        $orderRepository = app(\App\Repositories\OrderRepository::class);
        $job->handle($orderRepository);

        Mail::assertQueued(DailySalesReport::class, function ($mail) {
            $summary = $mail->salesSummary;
            return $summary['total_orders'] === 1
                && $summary['total_revenue'] === 250.00
                && $summary['total_items'] === 3; // 2 + 1
        });
    });
});

