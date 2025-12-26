<?php

namespace App\Http\Controllers\Api\Cart;

use App\Actions\Cart\RemoveFromCartAction;
use App\Http\Requests\CartRequest;
use Illuminate\Http\JsonResponse;

final class RemoveFromCartController
{
    public function __invoke(CartRequest $request, RemoveFromCartAction $action): JsonResponse
    {
        $action->execute($request->user(), $request->validated()['product_id']);

        return response()->json([
            'data' => null,
            'message' => 'Product removed from cart successfully',
            'status_code' => 200,
        ]);
    }
}

