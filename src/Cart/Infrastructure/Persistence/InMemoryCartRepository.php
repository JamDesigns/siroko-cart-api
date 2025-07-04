<?php

namespace App\Cart\Infrastructure\Persistence;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Repository\CartRepository;

class InMemoryCartRepository implements CartRepository
{
    /** @var Cart[] */
    private array $storage = [];

    public function find(string $id): ?Cart
    {
        return $this->storage[$id] ?? null;
    }

    public function save(Cart $cart): void
    {
        $this->storage[$cart->id()] = $cart;
    }

    // Additional method for testing or manual reset
    public function clear(): void
    {
        $this->storage = [];
    }

    public function all(): array
    {
        return $this->storage;
    }
}
