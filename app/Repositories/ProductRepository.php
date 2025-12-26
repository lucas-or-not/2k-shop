<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

final class ProductRepository
{
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    public function decrementStock(int $productId, int $quantity): bool
    {
        $product = $this->findById($productId);

        if (!$product) {
            return false;
        }

        $product->decrement('stock_quantity', $quantity);
        $product->refresh();

        // Low stock notification is handled by ProductObserver

        return true;
    }

    public function incrementStock(int $productId, int $quantity): bool
    {
        $product = $this->findById($productId);

        if (!$product) {
            return false;
        }

        $product->increment('stock_quantity', $quantity);

        return true;
    }
}

