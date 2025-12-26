<?php

namespace App\DTOs;

final class UpdateCartItemDto
{
    public function __construct(
        public int $productId,
        public int $quantity
    ) {}
}

