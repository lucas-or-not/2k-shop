<?php

namespace App\DTOs;

final class CartDto
{
    public function __construct(
        public int $productId,
        public int $quantity = 1
    ) {}
}

