<?php

namespace App\Cart\Infrastructure\Controller;

use App\Cart\Domain\Model\Currency;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Cart\Application\Command\ClearCartCommand;
use App\Cart\Application\Command\ClearCartHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Application\Command\RemoveProductFromCartCommand;
use App\Cart\Application\Command\RemoveProductFromCartHandler;
use App\Cart\Application\Command\UpdateProductQuantityCommand;
use App\Cart\Application\Command\UpdateProductQuantityHandler;
use App\Cart\Domain\Repository\CartRepository;

class CartModificationController
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly AddProductToCartHandler $addHandler,
        private readonly UpdateProductQuantityHandler $updateHandler,
        private readonly RemoveProductFromCartHandler $removeHandler,
        private readonly ClearCartHandler $clearHandler
    ) {}

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

            // Fetch the updated cart data
            $updatedCart = $this->cartRepository->find($id);

            $cartData = [
                'cart_id' => $updatedCart->id(),
                'products' => $updatedCart->items(),
                'total_amount' => $updatedCart->total()->amount(),
                'currency' => $updatedCart->total()->currency()->code(),
            ];

            return new JsonResponse([
                'message' => 'Quantity updated',
                'cart' => $cartData
            ], Response::HTTP_OK);

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

            // Fetch the updated cart data
            $updatedCart = $this->cartRepository->find($id);

            // If the cart is empty after removing the product, indicate that
            $cartData = [
                'cart_id' => $updatedCart->id(),
                'products' => $updatedCart->items(),
                'total_amount' => $updatedCart->total()->amount(),
                'currency' => $updatedCart->total()->currency()->code(),
            ];

            $message = 'Product removed';
            if (count($updatedCart->items()) === 0) {
                $message = $message;
                $cartData['products'] = [];
                $cartData['total_amount'] = 0;
            }

            return new JsonResponse([
                'message' => $message,
                'cart' => $cartData
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

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

            // Fetch the updated cart data
            $updatedCart = $this->cartRepository->find($id);

            $cartData = [
                'cart_id' => $updatedCart->id(),
                'products' => $updatedCart->items(),
                'total_amount' => $updatedCart->total()->amount(),
                'currency' => $updatedCart->total()->currency()->code(),
            ];

            return new JsonResponse([
                'message' => 'Product added to cart',
                'cart' => $cartData
            ], Response::HTTP_CREATED);

        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/cart/{id}', name: 'clear_cart', methods: ['DELETE'])]
    public function clearCart(string $id): Response
    {
        try {
            $command = new ClearCartCommand($id);
            ($this->clearHandler)($command);

            // After clearing the cart, return the empty cart data
            return new JsonResponse([
                'message' => 'Cart cleared',
                'cart' => [
                    'cart_id' => $id,
                    'products' => [],
                    'total_amount' => 0,
                    'currency' => 'EUR',  // Assuming EUR as a default currency
                ]
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
