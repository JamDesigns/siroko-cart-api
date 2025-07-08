<?php

namespace App\Cart\Application\Command;

use App\Cart\Domain\Repository\CartRepository;
use App\Shared\Domain\Event\GenericEvent;
use App\Shared\Domain\Event\EventBus;

class ClearCartHandler
{
    public function __construct(private readonly CartRepository $repository) {}

    public function __invoke(ClearCartCommand $command): void
    {
        // Find the cart to clear
        $cart = $this->repository->find($command->cartId);

        if (!$cart) {
            throw new \Exception("Cart not found");
        }

        // Dispatch event indicating cart is cleared
        EventBus::getInstance()->recordEvent(new GenericEvent(
            'CartCleared',
            'The cart has been cleared',
            [
                'cart_id' => $command->cartId
            ]
        ));

        // Perform the deletion of the cart
        $this->repository->delete($command->cartId);

        // Dispatch the events
        EventBus::getInstance()->dispatchEvents();
    }
}
