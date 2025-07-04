<?php

namespace App\Tests\Cart\Infrastructure\Controller;

use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\ProductId;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\Currency;

class GetCartControllerTest extends WebTestCase
{
    public function test_it_returns_cart_details(): void
    {
        echo "🧪 Running functional test: GET /cart/{id}\n";

        $client = static::createClient();
        $container = static::getContainer();

        /** @var InMemoryCartRepository $repo */
        $repo = $container->get(InMemoryCartRepository::class);

        // Prepare cart
        $cartId = 'test-cart-controller';
        $productId = new ProductId('00000000-0000-0000-0000-000000000999');
        $currency = new Currency('EUR');

        $cart = new Cart($cartId);
        $cart->addProduct($productId, new Quantity(2), new Money(1500, $currency)); // 30 €

        $repo->save($cart);

        // Request
        $client->request('GET', "/cart/{$cartId}");

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($cartId, $responseData['id']);
        $this->assertEquals(3000, $responseData['total']);
        $this->assertEquals('EUR', $responseData['currency']);
        $this->assertCount(1, $responseData['items']);
        $this->assertEquals('00000000-0000-0000-0000-000000000999', $responseData['items'][0]['productId']);
    }
}
