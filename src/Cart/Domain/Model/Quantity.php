<?php

namespace App\Cart\Domain\Model;

final class Quantity
{
    public function __construct(private readonly int $value)
    {
        if ($value < 1) {
            throw new \InvalidArgumentException("Quantity must be at least 1");
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public static function fromPrimitives(int $value): self
    {
        return new self($value);
    }

    public function toPrimitives(): int
    {
        return $this->value;
    }
}
