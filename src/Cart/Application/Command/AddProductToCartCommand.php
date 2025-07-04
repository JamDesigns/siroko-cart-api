<?php

namespace App\Cart\Application\Command;

use App\Cart\Domain\Model\Currency;

class AddProductToCartCommand
{
    public function __construct(
        public readonly string $cartId,
        public readonly string $productId,
        public readonly int $quantity,
        public readonly int $unitPriceInCents,
        public readonly Currency $currency
    ) {}
}
