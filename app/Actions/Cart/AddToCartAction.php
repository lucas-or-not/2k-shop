<?php

namespace App\Actions\Cart;

use App\DTOs\CartDto;
use App\Exceptions\CartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\OutOfStockException;
use App\Models\Cart;
use App\Models\User;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;

final class AddToCartAction
{
    public function __construct(
        private CartRepository $cartRepository,
        private ProductRepository $productRepository
    ) {}

    public function execute(User $user, CartDto $dto): Cart
    {
        $product = $this->productRepository->findById($dto->productId);
        
        throw_if(!$product, CartException::class, 'Product not found.');
        throw_if($product->stock_quantity === 0, OutOfStockException::class);
        throw_if($product->stock_quantity < $dto->quantity, InsufficientStockException::class, 
            "Insufficient stock. Available: {$product->stock_quantity}, Requested: {$dto->quantity}");

        $existingCartItem = $this->cartRepository->findCartItem($user, $dto->productId);
        
        if ($existingCartItem) {
            // If item was soft deleted, restore it and set to new quantity (don't add to old quantity)
            if ($existingCartItem->trashed()) {
                $existingCartItem->restore();
                // Set to the new quantity, not add to old quantity
                throw_if($product->stock_quantity < $dto->quantity, InsufficientStockException::class,
                    "Insufficient stock. Available: {$product->stock_quantity}, Requested: {$dto->quantity}");
                
                $this->cartRepository->updateCartItemQuantity($existingCartItem, $dto->quantity);
                return $existingCartItem->fresh();
            }
            
            // Item exists and is not deleted, add to existing quantity
            $newQuantity = $existingCartItem->quantity + $dto->quantity;
            throw_if($product->stock_quantity < $newQuantity, InsufficientStockException::class,
                "Insufficient stock. Available: {$product->stock_quantity}, Requested: {$newQuantity}");
            
            $this->cartRepository->updateCartItemQuantity($existingCartItem, $newQuantity);
            return $existingCartItem->fresh();
        }

        return $this->cartRepository->createCartItem($user, $dto->productId, $dto->quantity);
    }
}

