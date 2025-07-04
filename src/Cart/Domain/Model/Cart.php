<?php

namespace App\Cart\Domain\Model;

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

    public function addProduct(ProductId $productId, Quantity $quantity, Money $unitPrice): void
    {
        foreach ($this->items as $item) {
            if ($item->productId()->equals($productId)) {
                $newQty = new Quantity($item->quantity()->value() + $quantity->value());
                $item->updateQuantity($newQty);
                return;
            }
        }

        $this->items[] = new CartItem($productId, $quantity, $unitPrice);
    }

    public function removeProduct(ProductId $productId): void
    {
        $this->items = array_values(array_filter(
            $this->items,
            fn(CartItem $item) => !$item->productId()->equals($productId)
        ));
    }

    public function updateQuantity(ProductId $productId, Quantity $newQuantity): void
    {
        foreach ($this->items as $item) {
            if ($item->productId()->equals($productId)) {
                $item->updateQuantity($newQuantity);
                return;
            }
        }

        throw new ProductNotInCartException("Product not found in cart.");
    }

    public function hasProduct(ProductId $productId): bool
    {
        foreach ($this->items as $item) {
            if ($item->productId()->equals($productId)) {
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
}
