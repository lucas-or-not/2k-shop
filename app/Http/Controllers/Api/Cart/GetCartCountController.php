<?php

namespace App\Http\Controllers\Api\Cart;

use App\Actions\Cart\GetCartCountAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetCartCountController
{
    public function __invoke(Request $request, GetCartCountAction $action): JsonResponse
    {
        $count = $action->execute($request->user());

        return response()->json([
            'data' => ['count' => $count],
            'message' => 'Cart count retrieved successfully',
            'status_code' => 200,
        ]);
    }
}

