<?php

namespace App\Tests\Cart\Infrastructure\Controller;

use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Domain\Model\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;

class RemoveProductFromCartControllerTest extends WebTestCase
{
    public function test_it_removes_product_from_cart(): void
    {
        echo "🧪 Running functional test: DELETE /cart/{id}/items/{product}\n";

        $client = static::createClient();
        $container = static::getContainer();

        $repo = $container->get(DoctrineCartRepository::class);
        $handler = $container->get(AddProductToCartHandler::class);

        $cartId = 'test-cart-remove';
        $productId = '33333333-3333-3333-3333-333333333333';
        $currency = new Currency('EUR');

        // Preload cart using handler
        $handler(new AddProductToCartCommand(
            $cartId,
            $productId,
            1,
            1000,
            $currency
        ));

        // Send DELETE request
        $client->request('DELETE', "/cart/{$cartId}/items/{$productId}");

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Product removed', $response['message']);

        // Assert product was removed
        $updatedCart = $repo->find($cartId);
        $this->assertCount(0, $updatedCart->items());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartItemEntity')->execute();
        $em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartEntity')->execute();
    }
}
