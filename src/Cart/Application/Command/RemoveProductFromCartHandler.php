<?php

namespace App\Cart\Application\Command;

use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Repository\CartRepository;
use App\Shared\Domain\Event\GenericEvent;
use App\Shared\Domain\Event\EventBus;

class RemoveProductFromCartHandler
{
    public function __construct(private readonly CartRepository $repository) {}

    public function __invoke(RemoveProductFromCartCommand $command): void
    {
        // Find the cart to remove the product from
        $cart = $this->repository->find($command->cartId);

        if (!$cart) {
            throw new \Exception("Cart not found");
        }

        // Remove product from cart
        $cart->removeProduct(new Product($command->product));

        // Emit event indicating the product has been removed
        EventBus::getInstance()->recordEvent(new GenericEvent(
            'ProductRemovedFromCart',
            'Product removed from the cart',
            [
                'cart_id' => $command->cartId,
                'product_id' => $command->product
            ]
        ));

        // Save the updated cart
        $this->repository->save($cart);

        // Dispatch the events
        EventBus::getInstance()->dispatchEvents();
    }
}
