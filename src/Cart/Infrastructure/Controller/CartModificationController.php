<?php

namespace App\Cart\Infrastructure\Controller;

use App\Cart\Domain\Model\Currency;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Application\Command\UpdateProductQuantityCommand;
use App\Cart\Application\Command\UpdateProductQuantityHandler;
use App\Cart\Application\Command\RemoveProductFromCartHandler;
use App\Cart\Application\Command\RemoveProductFromCartCommand;

class CartModificationController
{
    public function __construct(
        private readonly AddProductToCartHandler $addHandler,
        private readonly UpdateProductQuantityHandler $updateHandler,
        private readonly RemoveProductFromCartHandler $removeHandler
    ) {}

    #[Route('/cart/{id}/items', name: 'add_cart_item', methods: ['POST'])]
    public function addProduct(string $id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['product'], $data['quantity'], $data['unitPrice'], $data['currency'])) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $command = new AddProductToCartCommand(
                $id,
                $data['product'],
                (int) $data['quantity'],
                (int) $data['unitPrice'],
                new Currency($data['currency'])
            );

            ($this->addHandler)($command);

            return new JsonResponse(['message' => 'Product added to cart'], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/cart/{id}/items/{product}', name: 'update_cart_item', methods: ['PATCH'])]
    public function updateProductQuantity(string $id, string $product, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['quantity'])) {
            return new JsonResponse(['error' => 'Missing "quantity" field'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $command = new UpdateProductQuantityCommand(
                $id,
                $product,
                (int) $data['quantity']
            );

            ($this->updateHandler)($command);

            return new JsonResponse(['message' => 'Quantity updated'], Response::HTTP_OK);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/cart/{id}/items/{product}', name: 'remove_cart_item', methods: ['DELETE'])]
    public function removeProduct(string $id, string $product): Response
    {
        try {
            $command = new RemoveProductFromCartCommand($id, $product);
            ($this->removeHandler)($command);

            return new JsonResponse(['message' => 'Product removed'], Response::HTTP_OK);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
