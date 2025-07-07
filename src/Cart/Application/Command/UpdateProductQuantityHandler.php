<?php

namespace App\Cart\Application\Command;

use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Repository\CartRepository;

class UpdateProductQuantityHandler
{
    public function __construct(private readonly CartRepository $repository) {}

    public function __invoke(UpdateProductQuantityCommand $command): void
    {
        $cart = $this->repository->find($command->cartId);

        if (!$cart) {
            return;
        }

        $cart->updateQuantity(
            new Product($command->product),
            new Quantity($command->newQuantity)
        );

        $this->repository->save($cart);
    }
}
