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
        $message = 'Order created successfully';  // Default success message
        $status = Response::HTTP_CREATED;  // Default HTTP status

        try {
            $command = new CheckoutCartCommand($cartId);
            $result = ($this->handler)($command);

            // Handle different results from the handler
            if ($result === 'cart_not_found') {
                $message = 'Cart not found during checkout';
                $status = Response::HTTP_NOT_FOUND;
            } elseif ($result === 'cart_empty') {
                $message = 'Cart is empty. Please add items to proceed.';
                $status = Response::HTTP_BAD_REQUEST;
            }

            // Return the response with the appropriate message
            return new JsonResponse(['message' => $message], $status);

        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}

