<?php

namespace App\Actions\Cart;

use App\DTOs\UpdateCartItemDto;
use App\Exceptions\CartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\OutOfStockException;
use App\Models\Cart;
use App\Models\User;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;

final class UpdateCartItemAction
{
    public function __construct(
        private CartRepository $cartRepository,
        private ProductRepository $productRepository
    ) {}

    public function execute(User $user, UpdateCartItemDto $dto): Cart
    {
        $product = $this->productRepository->findById($dto->productId);
        
        throw_if(!$product, CartException::class, 'Product not found.');
        throw_if($product->stock_quantity === 0, OutOfStockException::class);
        throw_if($product->stock_quantity < $dto->quantity, InsufficientStockException::class,
            "Insufficient stock. Available: {$product->stock_quantity}, Requested: {$dto->quantity}");

        $cartItem = $this->cartRepository->findActiveCartItem($user, $dto->productId);
        
        throw_if(!$cartItem, CartException::class, 'Product is not in your cart.');

        $this->cartRepository->updateCartItemQuantity($cartItem, $dto->quantity);
        
        return $cartItem->fresh(['product']);
    }
}

