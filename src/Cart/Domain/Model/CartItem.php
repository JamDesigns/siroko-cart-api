<?php

namespace App\Cart\Domain\Model;

final class CartItem
{
    public function __construct(
        private ProductId $productId,
        private Quantity $quantity,
        private Money $unitPrice
    ) {}

    public function productId(): ProductId
    {
        return $this->productId;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function totalPrice(): Money
    {
        return $this->unitPrice->multiply($this->quantity->value());
    }

    public function updateQuantity(Quantity $newQuantity): void
    {
        $this->quantity = $newQuantity;
    }
}
