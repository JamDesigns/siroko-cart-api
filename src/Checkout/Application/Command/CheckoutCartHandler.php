<?php

namespace App\Checkout\Application\Command;

use App\Cart\Domain\Repository\CartRepository;
use App\Checkout\Domain\Model\Order;
use App\Checkout\Domain\Repository\OrderRepository;
use App\Shared\Domain\Event\EventBus;
use App\Shared\Domain\Event\GenericEvent;

class CheckoutCartHandler
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly OrderRepository $orderRepository
    ) {}

    public function __invoke(CheckoutCartCommand $command): string
    {
        // Find the cart
        $cart = $this->cartRepository->find($command->cartId);

        // If the cart is not found, emit a "CartNotFound" event
        if (!$cart) {
            EventBus::getInstance()->recordEvent(new GenericEvent(
                'CartNotFound',
                'Cart not found during checkout',
                ['cart_id' => $command->cartId]
            ));
            EventBus::getInstance()->dispatchEvents();
            return 'cart_not_found';
        }

        // If the cart is empty, emit a "CartEmpty" event
        if (count($cart->items()) === 0) {
            EventBus::getInstance()->recordEvent(new GenericEvent(
                'CartEmpty',
                'Cart is empty during checkout',
                ['cart_id' => $command->cartId]
            ));
            EventBus::getInstance()->dispatchEvents();
            return 'cart_empty';
        }

        // Create the order from the cart
        $order = Order::fromCart($command->cartId, $cart);
        $this->orderRepository->save($order);

        // Empty the cart after creating the order
        $this->cartRepository->delete($command->cartId);

        // Emit an "OrderCreated" event after the order is created
        EventBus::getInstance()->recordEvent(new GenericEvent(
            'OrderCreated',
            'Order successfully created from the cart',
            [
                'order_id' => $order->id(),
                'cart_id' => $command->cartId,
                'total_amount' => $order->total(),
                'currency' => $order->currency()
            ]
        ));

        // Dispatch the events
        EventBus::getInstance()->dispatchEvents();

        return 'order_created';
    }
}
