<?php

namespace App\Tests\Cart\Domain\Model;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\Currency;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\ProductId;
use App\Cart\Domain\Model\Quantity;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    public function test_it_updates_quantity_if_product_already_in_cart(): void
    {
        echo "🔄 Running test: update existing product quantity in cart\n";

        $cart = new Cart('cart-001');
        $productId = new ProductId('123e4567-e89b-12d3-a456-426614174000');
        $currency = new Currency('EUR');

        // Add first time
        $cart->addProduct($productId, new Quantity(2), new Money(1000, $currency));

        // Add same reference again
        $cart->addProduct($productId, new Quantity(3), new Money(1000, $currency));

        $items = $cart->items();
        $this->assertCount(1, $items, 'There should only be one product line');
        $this->assertEquals(5, $items[0]->quantity()->value(), 'The amount must have been added');
        $this->assertEquals(5000, $items[0]->totalPrice()->amount(), 'Correct total price');
    }

    public function test_it_throws_when_updating_quantity_of_nonexistent_product(): void
    {
        echo "❌ Running test: update non-existent product\n";

        $cart = new Cart('cart-002');
        $nonExistentProductId = new ProductId('999e9999-9999-9999-9999-999999999999');

        $this->expectException(\App\Cart\Domain\Exception\ProductNotInCartException::class);

        $cart->updateQuantity($nonExistentProductId, new Quantity(3));
    }

    public function test_it_removes_a_product_from_cart(): void
    {
        echo "🗑️  Running test: remove product from cart\n";

        $cart = new Cart('cart-003');
        $productId1 = new ProductId('aaaabbbb-cccc-dddd-eeee-000000000001');
        $productId2 = new ProductId('aaaabbbb-cccc-dddd-eeee-000000000002');
        $currency = new Currency('EUR');

        // We added two different products
        $cart->addProduct($productId1, new Quantity(1), new Money(1000, $currency));
        $cart->addProduct($productId2, new Quantity(1), new Money(1500, $currency));

        // We assure that there are two products
        $this->assertCount(2, $cart->items());

        // We eliminated one
        $cart->removeProduct($productId1);

        $items = $cart->items();
        $this->assertCount(1, $items, 'There should be only one product left in the cart');
        $this->assertTrue($items[0]->productId()->equals($productId2), 'The remaining product must be the second');
    }

    public function test_it_calculates_total_price_correctly(): void
    {
        echo "🧾 Running test: calculate total price\n";

        $cart = new Cart('cart-004');
        $currency = new Currency('EUR');

        $product1 = new ProductId('aaaabbbb-0000-0000-0000-000000000001');
        $product2 = new ProductId('aaaabbbb-0000-0000-0000-000000000002');

        $cart->addProduct($product1, new Quantity(2), new Money(1000, $currency)); // 20 €
        $cart->addProduct($product2, new Quantity(3), new Money(1500, $currency)); // 45 €

        $total = $cart->total();

        $this->assertEquals(6500, $total->amount(), 'The total must be €65.00 (6500 cents)');
        $this->assertTrue($total->currency()->equals($currency), 'The same currency must be maintained');
    }

    public function test_it_checks_if_cart_has_product(): void
    {
        echo "🔍 Running test: check if product exists in cart\n";

        $cart = new Cart('cart-005');
        $productId1 = new ProductId('11111111-1111-1111-1111-111111111111');
        $productId2 = new ProductId('22222222-2222-2222-2222-222222222222');
        $currency = new Currency('EUR');

        $cart->addProduct($productId1, new Quantity(1), new Money(1000, $currency));

        $this->assertTrue($cart->hasProduct($productId1), 'The cart must contain 1 product');
        $this->assertFalse($cart->hasProduct($productId2), 'The cart must not contain product 2');
    }

    public function test_cart_has_id(): void
    {
        echo "🆔 Running test: cart id\n";

        $cart = new Cart('custom-cart-id');
        $this->assertEquals('custom-cart-id', $cart->id());
    }
}
