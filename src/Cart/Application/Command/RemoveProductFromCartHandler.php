<?php

namespace App\Cart\Application\Command;

use App\Cart\Domain\Model\Product;
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

        $cart->removeProduct(new Product($command->product));

        $this->repository->save($cart);
    }
}
