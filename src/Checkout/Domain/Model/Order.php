<?php

namespace App\Checkout\Domain\Model;

use App\Cart\Domain\Model\Cart;
use App\Checkout\Domain\Model\OrderItem;

class Order
{
    /** @var OrderItem[] */
    private array $items = [];

    public function __construct(
        private readonly string $id,
        private readonly string $currency,
        private readonly int $totalAmount
    ) {}

    public static function fromCart(string $orderId, Cart $cart): self
    {
        $currency = $cart->total()->currency()->code();
        $total = $cart->total()->amount();

        $order = new self($orderId, $currency, $total);

        foreach ($cart->items() as $item) {
            $order->items[] = new OrderItem(
                $item->product()->value(),
                $item->quantity()->value(),
                $item->unitPrice()->amount()
            );
        }

        return $order;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function total(): int
    {
        return $this->totalAmount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * @return OrderItem[]
     */
    public function items(): array
    {
        return $this->items;
    }
}
