<?php

namespace App\Cart\Infrastructure\Persistence\Doctrine;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Cart\Infrastructure\Persistence\Doctrine\CartEntity;
use App\Cart\Infrastructure\Persistence\Doctrine\CartItemEntity;

class DoctrineCartRepository implements CartRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(Cart $cart): void
    {
        $cartEntity = $this->em->find(CartEntity::class, $cart->id());

        if (!$cartEntity) {
            $cartEntity = new CartEntity($cart->id());
            $this->em->persist($cartEntity);
        }

        // Remove existing items (orphanRemoval=true)
        foreach ($cartEntity->getItems() as $existingItem) {
            $cartEntity->getItems()->removeElement($existingItem);
        }

        // Map Domain CartItems to CartItemEntities
        foreach ($cart->items() as $item) {
            $itemEntity = new CartItemEntity(
                $cartEntity,
                $item->product()->toPrimitives(),
                $item->quantity()->toPrimitives(),
                $item->unitPrice()->amount(),
                $item->unitPrice()->currency()->code()
            );

            $cartEntity->addItem($itemEntity);
        }

        $cartEntity->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }

    public function find(string $id): ?Cart
    {
        $cartEntity = $this->em->find(CartEntity::class, $id);
        if (!$cartEntity) {
            return null;
        }

        $items = [];
        foreach ($cartEntity->getItems() as $itemEntity) {
            $items[] = [
                'product'   => $itemEntity->getProductId(),
                'quantity'  => $itemEntity->getQuantity(),
                'unitPrice' => [
                    'amountInCents' => $itemEntity->getUnitPrice(),
                    'currency'      => $itemEntity->getCurrency(),
                ],
            ];
        }

        return Cart::fromPrimitives($cartEntity->getId(), $items);
    }

    public function delete(string $id): void
    {
        $cartEntity = $this->em->find(CartEntity::class, $id);
        if ($cartEntity) {
            $this->em->remove($cartEntity);
            $this->em->flush();
        }
    }

    public function all(): array
    {
        $cartEntities = $this->em->getRepository(CartEntity::class)->findAll();

        return array_map(function (CartEntity $entity) {
            $items = [];
            foreach ($entity->getItems() as $itemEntity) {
                $items[] = [
                    'product'   => $itemEntity->getProductId(),
                    'quantity'  => $itemEntity->getQuantity(),
                    'unitPrice' => [
                        'amountInCents' => $itemEntity->getUnitPrice(),
                        'currency'      => $itemEntity->getCurrency(),
                    ],
                ];
            }

            return Cart::fromPrimitives($entity->getId(), $items);
        }, $cartEntities);
    }
}
