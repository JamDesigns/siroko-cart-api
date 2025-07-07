<?php

namespace App\Cart\Application\Command;

use App\Cart\Domain\Repository\CartRepository;

class ClearCartHandler
{
    public function __construct(private readonly CartRepository $repository) {}

    public function __invoke(ClearCartCommand $command): void
    {
        $this->repository->delete($command->cartId);
    }
}
