<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class CartRepository
{
    public function getUserCartPaginated(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Cart::with('product')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getUserCart(User $user): Collection
    {
        return Cart::with('product')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findCartItem(User $user, int $productId): ?Cart
    {
        return Cart::withTrashed()
            ->with('product')
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();
    }

    public function findActiveCartItem(User $user, int $productId): ?Cart
    {
        return Cart::with('product')
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();
    }

    public function createCartItem(User $user, int $productId, int $quantity = 1): Cart
    {
        return Cart::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);
    }

    public function updateCartItemQuantity(Cart $cart, int $quantity): bool
    {
        $cart->quantity = $quantity;
        return $cart->save();
    }

    public function deleteCartItem(Cart $cart): bool
    {
        return $cart->delete();
    }

    public function clearUserCart(User $user): bool
    {
        // Force delete to completely remove cart items after order
        return Cart::where('user_id', $user->id)->forceDelete() > 0;
    }

    public function getCartCount(User $user): int
    {
        return Cart::where('user_id', $user->id)->count();
    }

    public function isInCart(User $user, int $productId): bool
    {
        return Cart::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();
    }
}

