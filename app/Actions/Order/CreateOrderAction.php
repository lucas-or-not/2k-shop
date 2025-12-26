<?php

namespace App\Actions\Order;

use App\Exceptions\CartException;
use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\User;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;

final class CreateOrderAction
{
    public function __construct(
        private OrderRepository $orderRepository,
        private CartRepository $cartRepository,
        private ProductRepository $productRepository
    ) {}

    public function execute(User $user): Order
    {
        $cartItems = $this->cartRepository->getUserCart($user);
        
        throw_if($cartItems->isEmpty(), CartException::class, 'Cart is empty.');

        return DB::transaction(function () use ($user, $cartItems) {
            $totalAmount = 0;
            $orderItems = [];

            // Validate stock and calculate total
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                $requestedQuantity = $cartItem->quantity;

                throw_if($product->stock_quantity < $requestedQuantity, 
                    InsufficientStockException::class,
                    "Insufficient stock for product: {$product->name}. Available: {$product->stock_quantity}, Requested: {$requestedQuantity}");

                $itemTotal = $product->price * $requestedQuantity;
                $totalAmount += $itemTotal;

                $orderItems[] = [
                    'product' => $product,
                    'quantity' => $requestedQuantity,
                    'price' => $product->price,
                ];
            }

            // Create order
            $order = $this->orderRepository->createOrder($user, $totalAmount, 'completed');

            // Create order items and decrement stock
            foreach ($orderItems as $item) {
                $this->orderRepository->createOrderItem(
                    $order,
                    $item['product']->id,
                    $item['quantity'],
                    $item['price']
                );

                $this->productRepository->decrementStock($item['product']->id, $item['quantity']);
            }

            // Clear cart
            $this->cartRepository->clearUserCart($user);

            return $order->load('orderItems.product');
        });
    }
}

