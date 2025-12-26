<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $product = $this->product;
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $product?->id,
            'quantity' => $this->quantity,
            'product' => [
                'id' => $product?->id,
                'name' => $product?->name,
                'description' => $product?->description,
                'price' => $product?->price,
                'stock_quantity' => $product?->stock_quantity,
            ],
            'subtotal' => $product ? ($product->price * $this->quantity) : 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
