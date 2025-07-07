<?php

namespace App\Checkout\Infrastructure\Persistence;

use App\Checkout\Domain\Model\Order;
use App\Checkout\Domain\Repository\OrderRepository;

class InMemoryOrderRepository implements OrderRepository
{
    /** @var Order[] */
    private array $orders = [];

    public function save(Order $order): void
    {
        $this->orders[$order->id()] = $order;
    }

    public function find(string $orderId): ?Order
    {
        return $this->orders[$orderId] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->orders);
    }
}
