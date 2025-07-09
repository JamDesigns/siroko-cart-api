<?php

namespace App\Tests\Checkout\Application\Command;

use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Domain\Model\Currency;
use App\Cart\Domain\Model\Product;
use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use App\Checkout\Application\Command\CheckoutCartCommand;
use App\Checkout\Application\Command\CheckoutCartHandler;
use App\Checkout\Infrastructure\Persistence\InMemoryOrderRepository;
use PHPUnit\Framework\TestCase;

class CheckoutCartHandlerTest extends TestCase
{
    public function test_it_generates_an_order_from_cart(): void
    {
        echo "🧪 Running integration test: CheckoutCartHandler\n";

        $cartId = 'checkout-cart-123';
        $product = new Product('44444444-4444-4444-4444-444444444444');
        $currency = new Currency('EUR');

        $cartRepo = new InMemoryCartRepository();
        $addHandler = new AddProductToCartHandler($cartRepo);

        ($addHandler)(new AddProductToCartCommand(
            $cartId,
            $product->value(),
            2,
            1500,
            $currency
        ));

        $orderRepo = new InMemoryOrderRepository();

        $handler = new CheckoutCartHandler($cartRepo, $orderRepo);
        $command = new CheckoutCartCommand($cartId);

        $handler($command);

        // Assert that an order has been saved
        $orders = (new \ReflectionClass($orderRepo))->getProperty('orders');
        $orders->setAccessible(true);
        $savedOrders = $orders->getValue($orderRepo);

        $this->assertCount(1, $savedOrders);

        /** @var \App\Checkout\Domain\Model\Order $order */
        $order = array_values($savedOrders)[0];

        $this->assertEquals(3000, $order->total());
        $this->assertEquals('EUR', $order->currency());
        $this->assertCount(1, $order->items());
        $this->assertEquals('44444444-4444-4444-4444-444444444444', $order->items()[0]->product());
    }
}
