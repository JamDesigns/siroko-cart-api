<?php

namespace App\Checkout\Infrastructure\Controller;

use App\Checkout\Domain\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class OrderQueryController extends AbstractController
{
    public function __construct(private readonly OrderRepository $repository) {}

    #[Route('/orders', name: 'order_list', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $orders = $this->repository->findAll();

        $data = array_map(function ($order) {
            return [
                'id' => $order->id(),
                'total' => $order->total(),
                'currency' => $order->currency(),
                'items' => array_map(fn ($item) => [
                    'product' => $item->getProduct(),
                    'quantity' => $item->getQuantity(),
                    'unitPrice' => $item->getUnitPrice(),
                ], $order->items()),
            ];
        }, $orders);

        return $this->json($data);
    }
}
