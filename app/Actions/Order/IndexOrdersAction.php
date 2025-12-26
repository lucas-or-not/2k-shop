<?php

namespace App\Actions\Order;

use App\Models\User;
use App\Repositories\OrderRepository;
use Illuminate\Pagination\LengthAwarePaginator;

final class IndexOrdersAction
{
    public function __construct(
        private OrderRepository $orderRepository
    ) {}

    public function execute(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->getUserOrders($user, $perPage);
    }
}

