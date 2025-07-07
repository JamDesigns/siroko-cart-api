<?php

namespace App\Cart\Infrastructure\Controller;

use App\Cart\Application\Command\EmptyCartCommand;
use App\Cart\Application\Command\EmptyCartHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartDeletionController
{
    public function __construct(private readonly EmptyCartHandler $handler) {}

    #[Route('/cart/{id}', name: 'delete_cart', methods: ['DELETE'])]
    public function __invoke(string $id): Response
    {
        try {
            ($this->handler)(new EmptyCartCommand($id));
            return new JsonResponse(['message' => 'Cart deleted'], Response::HTTP_OK);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
