<?php

namespace App\Http\Controllers\Api\Cart;

use App\Actions\Cart\IndexCartAction;
use App\Http\Resources\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class IndexCartController
{
    public function __invoke(Request $request, IndexCartAction $action): JsonResponse
    {
        $perPage = (int) ($request->get('perPage', 15));
        $cart = $action->execute($request->user(), $perPage);

        return response()->json([
            'data' => CartResource::collection($cart->items())->resolve(),
            'pagination' => [
                'current_page' => $cart->currentPage(),
                'last_page' => $cart->lastPage(),
                'per_page' => $cart->perPage(),
                'total' => $cart->total(),
            ],
            'message' => 'Cart retrieved successfully',
            'status_code' => 200,
        ]);
    }
}

