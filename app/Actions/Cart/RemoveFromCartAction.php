<?php

namespace App\Actions\Cart;

use App\Exceptions\CartException;
use App\Models\User;
use App\Repositories\CartRepository;

final class RemoveFromCartAction
{
    public function __construct(
        private CartRepository $cartRepository
    ) {}

    public function execute(User $user, int $productId): bool
    {
        $cartItem = $this->cartRepository->findActiveCartItem($user, $productId);
        
        throw_if(!$cartItem, CartException::class, 'Product is not in your cart.');

        return $this->cartRepository->deleteCartItem($cartItem);
    }
}

