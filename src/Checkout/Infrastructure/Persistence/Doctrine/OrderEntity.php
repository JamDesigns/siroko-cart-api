<?php

namespace App\Checkout\Infrastructure\Persistence\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class OrderEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'integer')]
    private int $total;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItemEntity::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $items;

    public function __construct(string $id, int $total, string $currency)
    {
        $this->id = $id;
        $this->total = $total;
        $this->currency = $currency;
        $this->items = new ArrayCollection();
    }

    public function addItem(OrderItemEntity $item): void
    {
        $this->items[] = $item;
        $item->setOrder($this);
    }

    public function id(): string
    {
        return $this->id;
    }

    /** @return OrderItemEntity[] */
    public function items(): array
    {
        return $this->items->toArray();
    }

    public function total(): int
    {
        return $this->total;
    }

    public function currency(): string
    {
        return $this->currency;
    }
}
