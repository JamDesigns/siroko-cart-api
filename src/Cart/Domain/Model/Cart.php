<?php

namespace App\Cart\Domain\Model;

use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\CartItem;
use App\Cart\Domain\Model\Currency;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Exception\ProductNotInCartException;

final class Cart
{
    /** @var CartItem[] */
    private array $items = [];

    public function __construct(private readonly string $id) {}

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
                return;
            }
        }

        $this->items[] = new CartItem($product, $quantity, $unitPrice);
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
}
