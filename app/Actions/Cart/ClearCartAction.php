<?php

namespace App\Actions\Cart;

use App\Models\User;
use App\Repositories\CartRepository;

final class ClearCartAction
{
    public function __construct(
        private CartRepository $cartRepository
    ) {}

    public function execute(User $user): bool
    {
        return $this->cartRepository->clearUserCart($user);
    }
}

