<?php

namespace App\Cart\Domain\Model;

final class Money
{
    public function __construct(
        private readonly int $amountInCents,
        private readonly Currency $currency
    ) {
        if ($amountInCents < 0) {
            throw new \InvalidArgumentException("Money cannot be negative");
        }
    }

    public function amount(): int
    {
        return $this->amountInCents;
    }

    public function currency(): Currency
    {
        return $this->currency;
    }

    public function multiply(int $factor): Money
    {
        return new Money($this->amountInCents * $factor, $this->currency);
    }

    public function isSameCurrency(Money $other): bool
    {
        return $this->currency->equals($other->currency());
    }
}
