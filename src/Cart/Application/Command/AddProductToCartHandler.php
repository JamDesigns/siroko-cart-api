<?php
namespace App\Cart\Application\Command;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\CartItem;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Repository\CartRepository;
use App\Shared\Domain\Event\EventBus;
use App\Shared\Domain\Event\GenericEvent;

class AddProductToCartHandler
{
    public function __construct(private readonly CartRepository $repository) {}

    public function __invoke(AddProductToCartCommand $command): void
    {
        // 1) Cargar o crear carrito
        $cart = $this->repository->find($command->cartId)
             ?? new Cart($command->cartId);

        // 2) Construir dominio
        $product  = new Product($command->product);
        $quantity = new Quantity($command->quantity);
        $money    = new Money($command->unitPriceInCents, $command->currency);

        // 3) Manipular colección de CartItem
        $items = $cart->items();
        $found = false;

        foreach ($items as &$item) {
            if ($item->product()->equals($product)) {
                // actualizar cantidad
                $newQty = new Quantity($item->quantity()->value() + $quantity->value());
                $item->updateQuantity($newQty);

                // evento de cantidad
                EventBus::getInstance()->recordEvent(new GenericEvent(
                    'ProductQuantityUpdatedInCart',
                    'Product quantity updated in cart',
                    [
                        'cart_id'    => $command->cartId,
                        'product_id' => $command->product,
                        'quantity'   => $newQty->value(),
                    ]
                ));

                $found = true;
                break;
            }
        }

        if (! $found) {
            // añadir item nuevo
            $items[] = new CartItem($product, $quantity, $money);

            // evento de añadido
            EventBus::getInstance()->recordEvent(new GenericEvent(
                'ProductAddedToCart',
                'Product added to cart',
                [
                    'cart_id'    => $command->cartId,
                    'product_id' => $command->product,
                    'quantity'   => $quantity->value(),
                    'unit_price' => $money->amount(),
                ]
            ));
        }

        // 4) Persistir los cambios en el modelo
        $cart->setItems($items);
        $this->repository->save($cart);

        // 5) Despachar todos los eventos
        EventBus::getInstance()->dispatchEvents();
    }
}
