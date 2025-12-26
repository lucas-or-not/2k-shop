<?php

namespace App\Observers;

use App\Jobs\LowStockNotificationJob;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductObserver
{
    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Check if stock_quantity was changed and is now low
        if ($product->wasChanged('stock_quantity')) {
            $lowStockThreshold = (int) config('app.low_stock_threshold', 10);
            
            // Dispatch notification if stock is low (above 0 but at or below threshold)
            if ($product->stock_quantity > 0 && $product->stock_quantity <= $lowStockThreshold) {
                // Only dispatch after transaction commits to avoid sending notifications for rolled back changes
                DB::afterCommit(function () use ($product) {
                    LowStockNotificationJob::dispatch($product);
                });
            }
        }
    }
}
