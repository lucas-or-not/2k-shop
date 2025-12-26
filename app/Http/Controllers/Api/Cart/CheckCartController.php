<?php

namespace App\Http\Controllers\Api\Cart;

use App\Actions\Cart\CheckCartAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CheckCartController
{
    public function __invoke(Request $request, int $productId, CheckCartAction $action): JsonResponse
    {
        $isInCart = $action->execute($request->user(), $productId);

        return response()->json([
            'is_in_cart' => $isInCart,
        ]);
    }
}

