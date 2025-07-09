<?php

namespace App\Tests\Cart\Infrastructure\Controller;

use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Domain\Model\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;

class UpdateProductQuantityControllerTest extends WebTestCase
{
    public function test_it_updates_quantity_of_existing_product(): void
    {
        echo "🧪 Running functional test: PATCH /cart/{id}/items/{product}\n";

        $client = static::createClient();
        $container = static::getContainer();

        $repo = $container->get(DoctrineCartRepository::class);
        $handler = $container->get(AddProductToCartHandler::class);

        $cartId = 'test-cart-update';
        $productId = '22222222-2222-2222-2222-222222222222';
        $currency = new Currency('EUR');

        // Preload cart using handler
        $handler(new AddProductToCartCommand(
            $cartId,
            $productId,
            1,
            1000,
            $currency
        ));

        $payload = ['quantity' => 5];

        $client->request(
            'PATCH',
            "/cart/{$cartId}/items/{$productId}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Quantity updated', $response['message']);

        // Assert quantity updated
        $updatedCart = $repo->find($cartId);
        $this->assertEquals(5, $updatedCart->items()[0]->quantity()->value());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartItemEntity')->execute();
        $em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartEntity')->execute();
    }
}
