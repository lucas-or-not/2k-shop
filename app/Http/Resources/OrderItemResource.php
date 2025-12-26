<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->relationLoaded('product') ? $this->product : null;
        
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product' => $product ? [
                'id' => $product->id ?? null,
                'name' => $product->name ?? null,
            ] : null,
            'quantity' => $this->quantity,
            'price_at_purchase' => $this->price_at_purchase,
            'subtotal' => $this->quantity * $this->price_at_purchase,
            'created_at' => $this->created_at,
        ];
    }
}
