<?php

namespace App\Tests\Cart\Application\Command;

use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\Currency;
use App\Cart\Domain\Model\ProductId;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Repository\CartRepository;
use PHPUnit\Framework\TestCase;

class AddProductToCartHandlerTest extends TestCase
{
    public function test_it_adds_a_product_to_cart(): void
    {
        echo "🛒 Running test: AddProductToCartHandlerTest\n";
        $cartId = 'cart-123';
        $productId = '11111111-1111-1111-1111-111111111111';
        $quantity = 2;
        $price = 1500; // 15,00 €
        $currency = new Currency('EUR');

        // Repository Mock
        $repository = $this->createMock(CartRepository::class);

        $repository->method('find')
            ->with($cartId)
            ->willReturn(null);

        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Cart $cart) use ($productId, $quantity, $price, $currency) {
                $items = $cart->items();
                $this->assertCount(1, $items);

                $item = $items[0];
                $this->assertTrue($item->productId()->equals(new ProductId($productId)));
                $this->assertEquals($quantity, $item->quantity()->value());
                $this->assertEquals($price, $item->unitPrice()->amount());
                $this->assertTrue($item->unitPrice()->currency()->equals($currency));

                return true;
            }));

        $handler = new AddProductToCartHandler($repository);

        $command = new AddProductToCartCommand(
            $cartId,
            $productId,
            $quantity,
            $price,
            $currency
        );

        $handler($command);
    }
}
