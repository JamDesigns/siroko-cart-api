<?php

namespace App\Tests\Checkout\Infrastructure\Controller;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\Currency;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\ProductId;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CheckoutControllerTest extends WebTestCase
{
    public function test_it_processes_checkout(): void
    {
        echo "🧪 Running functional test: POST /checkout/{cartId}\n";

        $client = static::createClient();
        $container = static::getContainer();

        /** @var InMemoryCartRepository $cartRepo */
        $cartRepo = $container->get(InMemoryCartRepository::class);

        $cartId = 'checkout-test-cart';
        $productId = new ProductId('55555555-5555-5555-5555-555555555555');
        $currency = new Currency('EUR');

        // Preload cart
        $cart = new Cart($cartId);
        $cart->addProduct($productId, new Quantity(2), new Money(1200, $currency));
        $cartRepo->save($cart);

        // Perform checkout
        $client->request('POST', "/checkout/{$cartId}");

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Order created successfully', $data['message']);
    }
}
