<?php

namespace App\Http\Controllers\Api\Cart;

use App\Actions\Cart\AddToCartAction;
use App\Http\Requests\CartRequest;
use App\Http\Resources\CartResource;
use Illuminate\Http\JsonResponse;

final class AddToCartController
{
    public function __invoke(CartRequest $request, AddToCartAction $action): JsonResponse
    {
        $cartItem = $action->execute($request->user(), $request->toDto());

        return response()->json([
            'data' => new CartResource($cartItem),
            'message' => 'Product added to cart successfully',
            'status_code' => 201,
        ], 201);
    }
}

