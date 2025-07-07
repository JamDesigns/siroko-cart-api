<?php

namespace App\Cart\Application\Command;

class ClearCartCommand
{
    public function __construct(public readonly string $cartId) {}
}
