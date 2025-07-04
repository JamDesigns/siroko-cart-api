<?php

namespace App\Cart\Application\Command;

class UpdateProductQuantityCommand
{
    public function __construct(
        public readonly string $cartId,
        public readonly string $productId,
        public readonly int $newQuantity
    ) {}
}
