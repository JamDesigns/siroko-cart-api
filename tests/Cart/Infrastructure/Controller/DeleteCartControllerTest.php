<?php

namespace App\Tests\Cart\Infrastructure\Controller;

use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Domain\Model\Currency;
use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

class DeleteCartControllerTest extends WebTestCase
{
    public function test_it_deletes_cart(): void
    {
        echo "🗑️  Running functional test: DELETE /cart/{id} (delete full cart)\n";

        $client = static::createClient();
        $container = static::getContainer();

        /** @var DoctrineCartRepository $repo */
        $repo = $container->get(DoctrineCartRepository::class);

        /** @var AddProductToCartHandler $handler */
        $handler = $container->get(AddProductToCartHandler::class);

        $cartId = Uuid::v4()->toRfc4122();
        $productId = Uuid::v4()->toRfc4122();

        // Preload cart using command handler
        $handler(new AddProductToCartCommand(
            $cartId,
            $productId,
            1,
            1000,
            new Currency('EUR')
        ));

        $this->assertNotNull($repo->find($cartId));

        // Perform DELETE request
        $client->request('DELETE', "/cart/{$cartId}");
        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Cart deleted', $response['message']);

        $this->assertNull($repo->find($cartId));
    }
}
