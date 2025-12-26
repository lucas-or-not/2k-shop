<?php

namespace App\Http\Controllers\Api\Order;

use App\Actions\Order\IndexOrdersAction;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class IndexOrdersController
{
    public function __invoke(Request $request, IndexOrdersAction $action): JsonResponse
    {
        $perPage = (int) ($request->get('perPage', 15));
        $orders = $action->execute($request->user(), $perPage);

        return response()->json([
            'data' => OrderResource::collection($orders->items())->resolve(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'message' => 'Orders retrieved successfully',
            'status_code' => 200,
        ]);
    }
}

