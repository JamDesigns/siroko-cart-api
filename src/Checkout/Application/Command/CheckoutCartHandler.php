<?php

namespace App\Checkout\Application\Command;

use App\Cart\Domain\Repository\CartRepository;
use App\Checkout\Domain\Model\Order;
use App\Checkout\Domain\Repository\OrderRepository;
use Symfony\Component\Uid\Uuid;

class CheckoutCartHandler
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly OrderRepository $orderRepository
    ) {}

    public function __invoke(CheckoutCartCommand $command): void
    {
        $cart = $this->cartRepository->find($command->cartId);

        if (!$cart) {
            throw new \RuntimeException('Cart not found.');
        }

        if (count($cart->items()) === 0) {
            throw new \RuntimeException('Cart is empty.');
        }

        $order = Order::fromCart(Uuid::v4()->toRfc4122(), $cart);
        $this->orderRepository->save($order);
    }
}
