<?php

namespace App\Cart\Domain\Model;

final class CartItem
{
    public function __construct(
        private Product $product,
        private Quantity $quantity,
        private Money $unitPrice
    ) {}

    public function product(): Product
    {
        return $this->product;
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

    public static function fromPrimitives(array $data): self
    {
        return new self(
            Product::fromPrimitives($data['product']),
            Quantity::fromPrimitives($data['quantity']),
            Money::fromPrimitives($data['unitPrice'])
        );
    }

    public function toPrimitives(): array
    {
        return [
            'product' => $this->product->toPrimitives(),
            'quantity' => $this->quantity->toPrimitives(),
            'unitPrice' => $this->unitPrice->toPrimitives(),
        ];
    }
}
