<?php

namespace App\Http\Controllers\Api\Cart;

use App\Actions\Cart\UpdateCartItemAction;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use Illuminate\Http\JsonResponse;

final class UpdateCartItemController
{
    public function __invoke(UpdateCartItemRequest $request, UpdateCartItemAction $action): JsonResponse
    {
        $cartItem = $action->execute($request->user(), $request->toDto());

        return response()->json([
            'data' => new CartResource($cartItem),
            'message' => 'Cart item updated successfully',
            'status_code' => 200,
        ]);
    }
}

