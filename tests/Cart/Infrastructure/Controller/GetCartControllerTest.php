<?php

namespace App\Tests\Cart\Infrastructure\Controller;

use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Domain\Model\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GetCartControllerTest extends WebTestCase
{
    public function test_it_returns_cart_details(): void
    {
        echo "🧪 Running functional test: GET /cart/{id}\n";

        $client = static::createClient();
        $container = static::getContainer();

        $repo = $container->get(\App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository::class);
        $handler = $container->get(AddProductToCartHandler::class);

        $cartId = 'test-cart-controller';
        $productId = '00000000-0000-0000-0000-000000000999';

        // Preload cart using handler
        $handler(new AddProductToCartCommand(
            $cartId,
            $productId,
            2,
            1500,
            new Currency('EUR')
        ));

        // Perform request
        $client->request('GET', "/cart/{$cartId}");

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($cartId, $responseData['id']);
        $this->assertEquals(3000, $responseData['total']);
        $this->assertEquals('EUR', $responseData['currency']);
        $this->assertCount(1, $responseData['items']);
        $this->assertEquals($productId, $responseData['items'][0]['product']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartItemEntity')->execute();
        $em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartEntity')->execute();
    }
}
