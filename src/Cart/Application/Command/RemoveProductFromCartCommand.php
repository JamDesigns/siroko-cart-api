<?php

namespace App\Cart\Application\Command;

class RemoveProductFromCartCommand
{
    public function __construct(
        public readonly string $cartId,
        public readonly string $product
    ) {}
}
