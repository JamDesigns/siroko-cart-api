<?php

namespace App\Cart\Application\Command;

use App\Cart\Domain\Model\ProductId;
use App\Cart\Domain\Repository\CartRepository;

class RemoveProductFromCartHandler
{
    public function __construct(private readonly CartRepository $repository) {}

    public function __invoke(RemoveProductFromCartCommand $command): void
    {
        $cart = $this->repository->find($command->cartId);

        if (!$cart) {
            return; // Optionally throw exception
        }

        $cart->removeProduct(new ProductId($command->productId));

        $this->repository->save($cart);
    }
}
