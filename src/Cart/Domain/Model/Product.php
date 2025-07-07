<?php

namespace App\Cart\Domain\Model;

final class Product
{
    public function __construct(private readonly string $value)
    {
        if (!preg_match('/^[a-f0-9\-]{36}$/', $value)) {
            throw new \InvalidArgumentException("Invalid UUID for Product");
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Product $other): bool
    {
        return $this->value === $other->value();
    }

    public static function fromPrimitives(string $value): self
    {
        return new self($value);
    }

    public function toPrimitives(): string
    {
        return $this->value;
    }
}
