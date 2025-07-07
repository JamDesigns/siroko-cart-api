<?php

namespace App\Checkout\Infrastructure\Persistence\Doctrine;

use App\Checkout\Domain\Model\Order;
use App\Checkout\Domain\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineOrderRepository implements OrderRepository
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function save(Order $order): void
    {
        $orderEntity = new OrderEntity(
            $order->id(),
            $order->total(),
            $order->currency()
        );

        foreach ($order->items() as $item) {
            $itemEntity = new OrderItemEntity(
                $item->product(),
                $item->quantity(),
                $item->unitPrice()
            );
            $orderEntity->addItem($itemEntity);
        }

        $this->em->persist($orderEntity);
        $this->em->flush();
    }

    public function find(string $orderId): ?Order
    {
        // Optional: implement later if needed
        return null;
    }
}
