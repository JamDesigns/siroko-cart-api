<?php

namespace App\Cart\Application\Command;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\ProductId;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Repository\CartRepository;

class AddProductToCartHandler
{
    public function __construct(private readonly CartRepository $repository) {}

    public function __invoke(AddProductToCartCommand $command): void
    {
        $cart = $this->repository->find($command->cartId);

        if (!$cart) {
            $cart = new Cart($command->cartId);
        }

        $cart->addProduct(
            new ProductId($command->productId),
            new Quantity($command->quantity),
            new Money($command->unitPriceInCents, $command->currency)
        );

        $this->repository->save($cart);
    }
}
