<?php

namespace App\Actions\Cart;

use App\Models\User;
use App\Repositories\CartRepository;

final class CheckCartAction
{
    public function __construct(
        private CartRepository $cartRepository
    ) {}

    public function execute(User $user, int $productId): bool
    {
        return $this->cartRepository->findActiveCartItem($user, $productId) !== null;
    }
}

