<?php

namespace App\Http\Controllers\Api\Order;

use App\Actions\Order\CreateOrderAction;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CreateOrderController
{
    public function __invoke(Request $request, CreateOrderAction $action): JsonResponse
    {
        $order = $action->execute($request->user());

        return response()->json([
            'data' => new OrderResource($order),
            'message' => 'Order created successfully',
            'status_code' => 201,
        ], 201);
    }
}

