<?php

namespace App\Cart\Domain\Model;

final class Cart
{
    /** @var CartItem[] */
    private array $items = [];

    public function __construct(private readonly string $id) {}

    public function id(): string
    {
        return $this->id;
    }

    /**
     * Returns the items (without mutating)
     *
     * @return CartItem[]
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Replaces the collection of items.
     *
     * @internal Usar sólo desde handlers
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
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
        return array_map(fn(CartItem $i) => $i->toPrimitives(), $this->items);
    }
}
