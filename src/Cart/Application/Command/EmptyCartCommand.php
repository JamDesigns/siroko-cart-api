<?php

namespace App\Cart\Application\Command;

class EmptyCartCommand
{
    public function __construct(public readonly string $cartId) {}
}
