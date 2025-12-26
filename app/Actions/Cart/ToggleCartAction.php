<?php

namespace App\Actions\Cart;

use App\DTOs\CartDto;
use App\Models\User;
use App\Repositories\CartRepository;

final class ToggleCartAction
{
    public function __construct(
        private CartRepository $cartRepository,
        private AddToCartAction $addToCartAction,
        private RemoveFromCartAction $removeFromCartAction
    ) {}

    public function execute(User $user, int $productId): array
    {
        $isInCart = $this->cartRepository->findActiveCartItem($user, $productId) !== null;

        if ($isInCart) {
            $this->removeFromCartAction->execute($user, $productId);
            $action = 'removed';
        } else {
            $dto = new CartDto(productId: $productId, quantity: 1);
            $this->addToCartAction->execute($user, $dto);
            $action = 'added';
        }

        return [
            'action' => $action,
            'is_in_cart' => !$isInCart,
            'count' => $this->cartRepository->getCartCount($user),
        ];
    }
}

