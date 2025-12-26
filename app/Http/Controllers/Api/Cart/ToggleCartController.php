<?php

namespace App\Http\Controllers\Api\Cart;

use App\Actions\Cart\ToggleCartAction;
use App\Http\Requests\CartRequest;
use Illuminate\Http\JsonResponse;

final class ToggleCartController
{
    public function __invoke(CartRequest $request, ToggleCartAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $request->validated()['product_id']);

        return response()->json([
            'data' => $result,
            'message' => "Product {$result['action']} from cart successfully",
            'status_code' => 200,
        ]);
    }
}

