<?php

namespace App\Cart\Application\Command;

class UpdateProductQuantityCommand
{
    public function __construct(
        public readonly string $cartId,
        public readonly string $product,
        public readonly int $newQuantity
    ) {}
}
