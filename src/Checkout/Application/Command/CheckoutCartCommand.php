<?php

namespace App\Checkout\Application\Command;

class CheckoutCartCommand
{
    public function __construct(
        public readonly string $cartId
    ) {}
}
