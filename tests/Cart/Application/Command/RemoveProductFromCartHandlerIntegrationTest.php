<?php

namespace App\Tests\Cart\Application\Command;

use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Application\Command\RemoveProductFromCartCommand;
use App\Cart\Application\Command\RemoveProductFromCartHandler;
use App\Cart\Domain\Model\Currency;
use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use PHPUnit\Framework\TestCase;

class RemoveProductFromCartHandlerIntegrationTest extends TestCase
{
    public function test_it_removes_product_using_handler(): void
    {
        echo "🗑️  Running integration test: RemoveProductFromCartHandler\n";

        $repo = new InMemoryCartRepository();
        $addHandler = new AddProductToCartHandler($repo);
        $removeHandler = new RemoveProductFromCartHandler($repo);

        $cartId = 'test-cart-4';
        $productId1 = '00000000-0000-0000-0000-000000000005';
        $productId2 = '00000000-0000-0000-0000-000000000006';
        $currency = new Currency('EUR');

        $addHandler(new AddProductToCartCommand($cartId, $productId1, 1, 1000, $currency));
        $addHandler(new AddProductToCartCommand($cartId, $productId2, 1, 1500, $currency));

        $removeHandler(new RemoveProductFromCartCommand($cartId, $productId1));

        $cart = $repo->find($cartId);

        $this->assertNotNull($cart);
        $this->assertCount(1, $cart->items());
        $this->assertEquals($productId2, $cart->items()[0]->productId()->value());
    }
}
