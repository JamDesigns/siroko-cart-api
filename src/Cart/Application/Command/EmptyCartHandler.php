<?php

namespace App\Cart\Application\Command;

use App\Cart\Domain\Repository\CartRepository;

class EmptyCartHandler
{
    public function __construct(private readonly CartRepository $repository) {}

    public function __invoke(EmptyCartCommand $command): void
    {
        $this->repository->delete($command->cartId);
    }
}
