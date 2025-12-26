<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\User;
use App\Repositories\CartRepository;
use Illuminate\Pagination\LengthAwarePaginator;

final class IndexProductsAction
{
    public function __construct(
        private CartRepository $cartRepository
    ) {}

    public function execute(?User $user, int $perPage = 12): LengthAwarePaginator
    {
        $products = Product::paginate($perPage);

        $pageProductIds = $products->pluck('id')->toArray();

        $cartItemIds = [];
        if ($user) {
            $cartItems = $this->cartRepository->getUserCart($user);
            $cartItemIds = $cartItems->whereIn('product_id', $pageProductIds)
                ->pluck('product_id')
                ->toArray();
        }

        $products->getCollection()->transform(function ($product) use ($cartItemIds) {
            $product->is_in_cart = in_array($product->id, $cartItemIds);
            return $product;
        });

        return $products;
    }
}

