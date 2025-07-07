<?php

namespace App\Cart\Infrastructure\Controller;

use App\Cart\Domain\Repository\CartRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController
{
    public function __construct(private readonly CartRepository $repository) {}

    #[Route('/cart/{id}', name: 'get_cart', methods: ['GET'])]
    public function __invoke(string $id): Response
    {
        $cart = $this->repository->find($id);

        if (!$cart) {
            return new JsonResponse(['error' => 'Cart not found'], Response::HTTP_NOT_FOUND);
        }

        $items = array_map(function ($item) {
            return [
                'product'   => $item->product()->value(),
                'quantity'    => $item->quantity()->value(),
                'unitPrice'   => $item->unitPrice()->amount(),
                'currency'    => $item->unitPrice()->currency()->code(),
                'total'       => $item->totalPrice()->amount(),
            ];
        }, $cart->items());

        return new JsonResponse([
            'id'     => $cart->id(),
            'items'  => $items,
            'total'  => $cart->total()->amount(),
            'currency' => $cart->total()->currency()->code(),
        ]);
    }
}
