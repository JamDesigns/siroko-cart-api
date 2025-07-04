<?php

namespace App\Tests\Cart\Application\Command;

use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Application\Command\UpdateProductQuantityCommand;
use App\Cart\Application\Command\UpdateProductQuantityHandler;
use App\Cart\Domain\Model\Currency;
use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use PHPUnit\Framework\TestCase;

class UpdateProductQuantityHandlerIntegrationTest extends TestCase
{
    public function test_it_updates_product_quantity(): void
    {
        echo "✏️  Running integration test: UpdateProductQuantityHandler\n";

        $repo = new InMemoryCartRepository();
        $addHandler = new AddProductToCartHandler($repo);
        $updateHandler = new UpdateProductQuantityHandler($repo);

        $cartId = 'test-cart-5';
        $productId = '00000000-0000-0000-0000-000000000007';
        $currency = new Currency('EUR');

        // Add product with quantity 2
        $addHandler(new AddProductToCartCommand($cartId, $productId, 2, 1000, $currency));

        // Update quantity to 5
        $updateHandler(new UpdateProductQuantityCommand($cartId, $productId, 5));

        $cart = $repo->find($cartId);

        $this->assertNotNull($cart);
        $this->assertCount(1, $cart->items());
        $this->assertEquals(5, $cart->items()[0]->quantity()->value());
        $this->assertEquals(5000, $cart->items()[0]->totalPrice()->amount());
    }

    public function test_it_throws_if_product_does_not_exist(): void
    {
        echo "💥 Running integration test: update non-existent product quantity\n";

        $repo = new InMemoryCartRepository();
        $updateHandler = new UpdateProductQuantityHandler($repo);

        $cartId = 'test-cart-6';
        $productId = '00000000-0000-0000-0000-000000000099';

        // Manually create a cart (empty, without products)
        $repo->save(new \App\Cart\Domain\Model\Cart($cartId));

        $this->expectException(\App\Cart\Domain\Exception\ProductNotInCartException::class);

        // Attempt to update a product that doesn't exist
        $updateHandler(new UpdateProductQuantityCommand($cartId, $productId, 3));
    }
}
