<?php

namespace App\Tests\Cart\Infrastructure\Controller;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Currency;
use App\Cart\Domain\Model\Quantity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;

class GetCartControllerTest extends WebTestCase
{
    public function test_it_returns_cart_details(): void
    {
        echo "🧪 Running functional test: GET /cart/{id}\n";

        $client = static::createClient();
        $container = static::getContainer();

        /** @var InMemoryCartRepository $repo */
        $repo = $container->get(DoctrineCartRepository::class);

        // Prepare cart
        $cartId = 'test-cart-controller';
        $product = new Product('00000000-0000-0000-0000-000000000999');
        $currency = new Currency('EUR');

        $cart = new Cart($cartId);
        $cart->addProduct($product, new Quantity(2), new Money(1500, $currency)); // 30 €

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
        $this->assertEquals('00000000-0000-0000-0000-000000000999', $responseData['items'][0]['product']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartItemEntity')->execute();
        $em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartEntity')->execute();
    }
}
