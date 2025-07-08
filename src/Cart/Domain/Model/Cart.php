<?php

namespace App\Cart\Domain\Model;

use App\Shared\Domain\Event\EventBus;
use App\Shared\Domain\Event\GenericEvent;
use App\Cart\Domain\Exception\ProductNotInCartException;

final class Cart
{
    /** @var CartItem[] */
    private array $items = [];

    public function __construct(private readonly string $id) {
        // Constructor remains the same, no EventBus injection
    }

    public function id(): string
    {
        return $this->id;
    }

    public function items(): array
    {
        return $this->items;
    }

    public function addProduct(Product $product, Quantity $quantity, Money $unitPrice): void
    {
        foreach ($this->items as $item) {
            if ($item->product()->equals($product)) {
                $newQty = new Quantity($item->quantity()->value() + $quantity->value());
                $item->updateQuantity($newQty);

                // Emit event using the EventBus singleton
                EventBus::getInstance()->recordEvent(new GenericEvent(
                    'ProductUpdatedInCart',
                    'Product quantity updated in the cart',
                    [
                        'cart_id' => $this->id(),
                        'product_id' => $product->value(),
                        'quantity' => $newQty->value()
                    ]
                ));

                return;
            }
        }

        // New product added
        $this->items[] = new CartItem($product, $quantity, $unitPrice);

        // Emit event using the EventBus singleton
        EventBus::getInstance()->recordEvent(new GenericEvent(
            'ProductAddedToCart',
            'Product added to the cart',
            [
                'cart_id' => $this->id(),
                'product_id' => $product->value(),
                'quantity' => $quantity->value(),
                'unit_price' => $unitPrice->amount()
            ]
        ));
    }

    public function removeProduct(Product $product): void
    {
        $this->items = array_values(array_filter(
            $this->items,
            fn(CartItem $item) => !$item->product()->equals($product)
        ));
    }

    public function updateQuantity(Product $product, Quantity $newQuantity): void
    {
        foreach ($this->items as $item) {
            if ($item->product()->equals($product)) {
                $item->updateQuantity($newQuantity);
                return;
            }
        }

        throw new ProductNotInCartException("Product not found in cart.");
    }

    public function hasProduct(Product $product): bool
    {
        foreach ($this->items as $item) {
            if ($item->product()->equals($product)) {
                return true;
            }
        }

        return false;
    }

    public function total(): Money
    {
        $total = 0;
        $currency = null;

        foreach ($this->items as $item) {
            if ($currency === null) {
                $currency = $item->unitPrice()->currency();
            }

            $total += $item->totalPrice()->amount();
        }

        return new Money($total, $currency ?? new Currency('EUR'));
    }

    public static function fromPrimitives(string $id, array $items): self
    {
        // Get the EventBus instance
        $eventBus = EventBus::getInstance();

        // Create the cart using the EventBus
        $cart = new self($id);

        foreach ($items as $itemData) {
            $cart->items[] = CartItem::fromPrimitives($itemData);
        }

        return $cart;
    }

    public function toPrimitives(): array
    {
        return array_map(
            fn(CartItem $item) => $item->toPrimitives(),
            $this->items
        );
    }

    public function dispatchEvents(): void
    {
        EventBus::getInstance()->dispatchEvents();
    }
}
