<?php

namespace App\Actions\Cart;

use App\Models\User;
use App\Repositories\CartRepository;

final class GetCartCountAction
{
    public function __construct(
        private CartRepository $cartRepository
    ) {}

    public function execute(User $user): int
    {
        return $this->cartRepository->getCartCount($user);
    }
}

