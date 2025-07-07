<?php

namespace App\Tests\Cart\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\Currency;
use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;

class ClearCartControllerTest extends WebTestCase
{
    public function test_it_clears_cart(): void
    {
        echo "🧪 Running functional test: DELETE /cart/{id}\n";

        $client = static::createClient();
        $container = static::getContainer();

        /** @var DoctrineCartRepository $repo */
        $repo = $container->get(DoctrineCartRepository::class);

        $cartId = 'test-cart-clear';
        $product = new Product('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa');
        $currency = new Currency('EUR');

        // Preload cart
        $cart = new Cart($cartId);
        $cart->addProduct($product, new Quantity(2), new Money(1000, $currency));
        $repo->save($cart);

        // Send DELETE request
        $client->request('DELETE', "/cart/{$cartId}");

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Cart deleted', $response['message']);

        // Assert cart is gone
        $this->assertNull($repo->find($cartId), 'Cart should be deleted');
    }
}
