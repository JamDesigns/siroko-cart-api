<?php

namespace App\Cart\Application\Command;

use App\Cart\Domain\Repository\CartRepository;
use App\Shared\Domain\Event\GenericEvent;
use App\Shared\Domain\Event\EventBus;

class EmptyCartHandler
{
    public function __construct(private readonly CartRepository $repository) {}

    public function __invoke(EmptyCartCommand $command): void
    {
        // Find the cart to empty
        $cart = $this->repository->find($command->cartId);

        if (!$cart) {
            throw new \Exception("Cart not found");
        }

        // Dispatch event indicating the cart has been emptied
        EventBus::getInstance()->recordEvent(new GenericEvent(
            'CartEmptied',
            'The cart has been emptied',
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
