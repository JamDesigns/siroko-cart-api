<?php

namespace App\Checkout\Infrastructure\Controller;

use App\Checkout\Application\Command\CheckoutCartCommand;
use App\Checkout\Application\Command\CheckoutCartHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController
{
    public function __construct(private readonly CheckoutCartHandler $handler) {}

    #[Route('/checkout/{cartId}', name: 'checkout_cart', methods: ['POST'])]
    public function __invoke(string $cartId): Response
    {
        try {
            $command = new CheckoutCartCommand($cartId);
            ($this->handler)($command);

            return new JsonResponse(['message' => 'Order created successfully'], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
