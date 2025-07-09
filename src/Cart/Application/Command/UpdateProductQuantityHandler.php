<?php
namespace App\Cart\Application\Command;

use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Repository\CartRepository;
use App\Shared\Domain\Event\EventBus;
use App\Shared\Domain\Event\GenericEvent;

class UpdateProductQuantityHandler
{
    public function __construct(private readonly CartRepository $repository) {}

    public function __invoke(UpdateProductQuantityCommand $command): void
    {
        $cart = $this->repository->find($command->cartId)
             ?? throw new \Exception("Cart not found");

        $product  = new Product($command->product);
        $newQty   = new Quantity($command->newQuantity);
        $updated  = false;

        $items = $cart->items();
        foreach ($items as &$item) {
            if ($item->product()->equals($product)) {
                $item->updateQuantity($newQty);
                $updated = true;
                break;
            }
        }
        if (! $updated) {
            throw new \Exception("Product not found in cart");
        }

        EventBus::getInstance()->recordEvent(new GenericEvent(
            'ProductQuantityUpdatedInCart',
            'Product quantity updated in cart',
            [
                'cart_id'    => $command->cartId,
                'product_id' => $command->product,
                'quantity'   => $newQty->value(),
            ]
        ));

        $cart->setItems($items);
        $this->repository->save($cart);
        EventBus::getInstance()->dispatchEvents();
    }
}
