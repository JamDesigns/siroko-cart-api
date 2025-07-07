<?php

namespace App\Cart\Domain\Repository;

use App\Cart\Domain\Model\Cart;

interface CartRepository
{
    public function find(string $id): ?Cart;

    public function save(Cart $cart): void;

    public function delete(string $id): void;

    public function all(): array;
}
