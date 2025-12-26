<?php

namespace App\Http\Controllers\Api;

use App\Actions\Product\IndexProductsAction;
use App\Actions\Product\ShowProductAction;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProductController
{
    /**
     * Get all products
     */
    public function index(Request $request, IndexProductsAction $action): AnonymousResourceCollection|JsonResponse
    {
        $perPage = (int) ($request->get('perPage', 15));
        $products = $action->execute($request->user(), $perPage);

        if ($products->isEmpty()) {
            return response()->json([
                'data' => [],
                'message' => 'No products to display',
                'status_code' => 200,
            ]);
        }

        return ProductResource::collection($products);
    }

    /**
     * Get single product
     */
    public function show(Request $request, int $id, ShowProductAction $action): JsonResponse
    {
        $product = $action->execute($id, $request->user());

        if (!$product) {
            return response()->json([
                'data' => null,
                'message' => 'Product not found',
                'status_code' => 404,
            ], 404);
        }

        return response()->json([
            'data' => new ProductResource($product),
            'message' => 'Product retrieved successfully',
            'status_code' => 200,
        ]);
    }
}
