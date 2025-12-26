<?php

namespace App\Http\Controllers\Api\Cart;

use App\Actions\Cart\ClearCartAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ClearCartController
{
    public function __invoke(Request $request, ClearCartAction $action): JsonResponse
    {
        $action->execute($request->user());

        return response()->json([
            'data' => null,
            'message' => 'Cart cleared successfully',
            'status_code' => 200,
        ]);
    }
}

