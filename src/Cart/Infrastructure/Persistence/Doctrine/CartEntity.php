<?php

namespace App\Cart\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Cart\Infrastructure\Persistence\Doctrine\CartItemEntity;

#[ORM\Entity]
#[ORM\Table(name: 'carts')]
class CartEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartItemEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(CartItemEntity $item): void
    {
        $this->items->add($item);
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
