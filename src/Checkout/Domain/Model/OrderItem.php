<?php

namespace App\Checkout\Domain\Model;

class OrderItem
{
    public function __construct(
        private readonly string $productId,
        private readonly int $quantity,
        private readonly int $unitPrice
    ) {}

    public function productId(): string
    {
        return $this->productId;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function unitPrice(): int
    {
        return $this->unitPrice;
    }

    public function subtotal(): int
    {
        return $this->quantity * $this->unitPrice;
    }
}
