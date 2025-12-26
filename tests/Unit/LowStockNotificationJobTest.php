<?php

use App\Jobs\LowStockNotificationJob;
use App\Mail\LowStockNotification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('LowStockNotificationJob', function () {
    it('sends email to admin when product stock is low', function () {
        Mail::fake();
        
        $admin = User::factory()->create([
            'email' => config('mail.admin_email', 'admin@2kshop.com'),
        ]);
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'stock_quantity' => 5,
        ]);

        $job = new LowStockNotificationJob($product);
        $job->handle();

        Mail::assertQueued(LowStockNotification::class, function ($mail) use ($product, $admin) {
            return $mail->hasTo($admin->email) 
                && $mail->product->id === $product->id;
        });
    });

    it('does not send email if admin user does not exist', function () {
        Mail::fake();
        
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'stock_quantity' => 5,
        ]);

        $job = new LowStockNotificationJob($product);
        $job->handle();

        Mail::assertNothingQueued();
    });

    it('includes product details in email', function () {
        Mail::fake();
        
        $admin = User::factory()->create([
            'email' => config('mail.admin_email', 'admin@2kshop.com'),
        ]);
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock_quantity' => 3,
        ]);

        $job = new LowStockNotificationJob($product);
        $job->handle();

        Mail::assertQueued(LowStockNotification::class, function ($mail) use ($product) {
            return $mail->product->name === $product->name
                && $mail->product->stock_quantity === 3;
        });
    });
});

