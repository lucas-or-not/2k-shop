<?php

namespace App\Actions\Cart;

use App\Models\User;
use App\Repositories\CartRepository;
use Illuminate\Pagination\LengthAwarePaginator;

final class IndexCartAction
{
    public function __construct(
        private CartRepository $cartRepository
    ) {}

    public function execute(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->cartRepository->getUserCartPaginated($user, $perPage);
    }
}

