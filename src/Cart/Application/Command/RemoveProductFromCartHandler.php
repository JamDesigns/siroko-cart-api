<?php
namespace App\Cart\Application\Command;

use App\Cart\Domain\Model\CartItem;
use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Repository\CartRepository;
use App\Shared\Domain\Event\EventBus;
use App\Shared\Domain\Event\GenericEvent;

class RemoveProductFromCartHandler
{
    public function __construct(private readonly CartRepository $repository) {}

    public function __invoke(RemoveProductFromCartCommand $command): void
    {
        $cart = $this->repository->find($command->cartId)
             ?? throw new \Exception("Cart not found");

        // Filter items except the one we deleted
        $newItems = array_filter(
            $cart->items(),
            fn(CartItem $i) => ! $i->product()->equals(new Product($command->product))
        );

        EventBus::getInstance()->recordEvent(new GenericEvent(
            'ProductRemovedFromCart',
            'Product removed from cart',
            [
                'cart_id'    => $command->cartId,
                'product_id' => $command->product,
            ]
        ));

        $cart->setItems(array_values($newItems));
        $this->repository->save($cart);
        EventBus::getInstance()->dispatchEvents();
    }
}
