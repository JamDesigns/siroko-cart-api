<?php

namespace App\Checkout\Domain\Repository;

use App\Checkout\Domain\Model\Order;

interface OrderRepository
{
    public function save(Order $order): void;

    public function find(string $orderId): ?Order;
}
