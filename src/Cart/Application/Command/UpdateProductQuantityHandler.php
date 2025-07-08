<?php

namespace App\Cart\Application\Command;

use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Repository\CartRepository;
use App\Shared\Domain\Event\GenericEvent;
use App\Shared\Domain\Event\EventBus;

class UpdateProductQuantityHandler
{
    public function __construct(private readonly CartRepository $repository) {}

    public function __invoke(UpdateProductQuantityCommand $command): void
    {
        // Find the cart to update the product quantity
        $cart = $this->repository->find($command->cartId);

        if (!$cart) {
            throw new \Exception("Cart not found");
        }

        // Update the product quantity in the cart
        $cart->updateQuantity(
            new Product($command->product),
            new Quantity($command->newQuantity)
        );

        // Emit event indicating the product quantity has been updated
        EventBus::getInstance()->recordEvent(new GenericEvent(
            'ProductQuantityUpdatedInCart',
            'Product quantity updated in the cart',
            [
                'cart_id' => $command->cartId,
                'product_id' => $command->product,
                'new_quantity' => $command->newQuantity
            ]
        ));

        // Save the updated cart
        $this->repository->save($cart);

        // Dispatch the events
        EventBus::getInstance()->dispatchEvents();
    }
}
