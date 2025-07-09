<?php

namespace App\Tests\Checkout\Infrastructure\Controller;

use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Domain\Model\Currency;
use App\Cart\Domain\Model\Product;
use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CheckoutControllerTest extends WebTestCase
{
    public function test_it_processes_checkout(): void
    {
        echo "🧪 Running functional test: POST /checkout/{cartId}\n";

        $client = static::createClient();
        $container = static::getContainer();

        /** @var DoctrineCartRepository $cartRepo */
        $cartRepo = $container->get(DoctrineCartRepository::class);
        $addHandler = new AddProductToCartHandler($cartRepo);

        $cartId = 'checkout-test-cart';
        $product = new Product('55555555-5555-5555-5555-555555555555');
        $currency = new Currency('EUR');

        ($addHandler)(new AddProductToCartCommand(
            $cartId,
            $product->value(),
            2,
            1200,
            $currency
        ));

        $client->request('POST', "/checkout/{$cartId}");

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Order created successfully', $data['message']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartItemEntity')->execute();
        $em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartEntity')->execute();
        $em->createQuery('DELETE FROM App\Checkout\Infrastructure\Persistence\Doctrine\OrderItemEntity')->execute();
        $em->createQuery('DELETE FROM App\Checkout\Infrastructure\Persistence\Doctrine\OrderEntity')->execute();
    }
}
