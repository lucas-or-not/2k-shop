<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\User;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;

final class ShowProductAction
{
    public function __construct(
        private ProductRepository $productRepository,
        private CartRepository $cartRepository
    ) {}

    public function execute(int $id, ?User $user): ?Product
    {
        $product = $this->productRepository->findById($id);
        
        if ($product && $user) {
            $product->is_in_cart = $this->cartRepository->findActiveCartItem($user, $id) !== null;
        }
        
        return $product;
    }
}

