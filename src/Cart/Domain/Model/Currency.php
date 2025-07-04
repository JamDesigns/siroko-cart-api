<?php

namespace App\Cart\Domain\Model;

final class Currency
{
    public function __construct(private readonly string $code)
    {
        if (!in_array($code, ['EUR', 'USD', 'GBP'])) {
            throw new \InvalidArgumentException("Unsupported currency: {$code}");
        }
    }

    public function code(): string
    {
        return $this->code;
    }

    public function equals(Currency $other): bool
    {
        return $this->code === $other->code();
    }
}
