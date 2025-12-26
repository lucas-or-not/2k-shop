<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class OrderRepository
{
    public function createOrder(User $user, float $totalAmount, string $status = 'pending'): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'total_amount' => $totalAmount,
            'status' => $status,
        ]);
    }

    public function createOrderItem(Order $order, int $productId, int $quantity, float $priceAtPurchase): OrderItem
    {
        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price_at_purchase' => $priceAtPurchase,
        ]);
    }

    public function getDailyOrders(Carbon $date): Collection
    {
        return Order::with(['orderItems.product', 'user'])
            ->whereDate('created_at', $date->toDateString())
            ->where('status', 'completed')
            ->get();
    }

    public function getDailySalesSummary(Carbon $date): array
    {
        $orders = $this->getDailyOrders($date);

        $totalRevenue = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        $totalItems = $orders->sum(fn($order) => $order->orderItems->sum('quantity'));

        $productBreakdown = [];
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $productId = $item->product_id;
                $productName = $item->product->name ?? 'Unknown';

                if (!isset($productBreakdown[$productId])) {
                    $productBreakdown[$productId] = [
                        'name' => $productName,
                        'quantity' => 0,
                        'revenue' => 0,
                    ];
                }

                $productBreakdown[$productId]['quantity'] += $item->quantity;
                $productBreakdown[$productId]['revenue'] += $item->quantity * $item->price_at_purchase;
            }
        }

        return [
            'date' => $date->toDateString(),
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'total_items' => $totalItems,
            'product_breakdown' => array_values($productBreakdown),
        ];
    }

    public function getUserOrders(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Order::with(['orderItems.product'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

