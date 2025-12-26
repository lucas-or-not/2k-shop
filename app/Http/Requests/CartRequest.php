<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Product ID is required.',
            'product_id.integer' => 'Product ID must be a number.',
            'product_id.exists' => 'Product not found.',
            'quantity.integer' => 'Quantity must be a number.',
            'quantity.min' => 'Quantity must be at least 1.',
        ];
    }

    public function toDto(): \App\DTOs\CartDto
    {
        return new \App\DTOs\CartDto(
            productId: $this->validated()['product_id'],
            quantity: $this->validated()['quantity'] ?? 1
        );
    }
} 