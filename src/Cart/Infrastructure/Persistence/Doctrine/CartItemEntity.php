<?php

namespace App\Cart\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cart_items')]
class CartItemEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: CartEntity::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'cart_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private CartEntity $cart;

    #[ORM\Column(type: 'string', length: 36)]
    private string $product;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'integer')]
    private int $unitPrice;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    public function __construct(CartEntity $cart, string $product, int $quantity, int $unitPrice, string $currency)
    {
        $this->cart = $cart;
        $this->product = $product;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->currency = $currency;
    }

    public function getProductId(): string
    {
        return $this->product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
