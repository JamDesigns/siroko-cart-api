<?php

namespace App\Cart\Infrastructure\Controller;

use App\Cart\Domain\Repository\CartRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartQueryController
{
    public function __construct(private readonly CartRepository $repository) {}

    #[Route('/carts', name: 'get_all_carts', methods: ['GET'])]
    public function __invoke(): Response
    {
        $carts = $this->repository->all();

        $response = array_map(function ($cart) {
            return [
                'id'       => $cart->id(),
                'total'    => $cart->total()->amount(),
                'currency' => $cart->total()->currency()->code(),
                'items'    => array_map(function ($item) {
                    return [
                        'product'    => $item->product()->toPrimitives(),
                        'quantity'   => $item->quantity()->toPrimitives(),
                        'unitPrice'  => $item->unitPrice()->amount(),
                        'total'      => $item->totalPrice()->amount(),
                    ];
                }, $cart->items()),
            ];
        }, $carts);

        return new JsonResponse($response, Response::HTTP_OK);
    }
}
