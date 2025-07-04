<?php

namespace App\Cart\Domain\Model;

final class ProductId
{
    public function __construct(private readonly string $value)
    {
        if (!preg_match('/^[a-f0-9\-]{36}$/', $value)) {
            throw new \InvalidArgumentException("Invalid UUID for ProductId");
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(ProductId $other): bool
    {
        return $this->value === $other->value();
    }
}
