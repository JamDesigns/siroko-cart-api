<?php

namespace App\Tests\Cart\Infrastructure\Controller;

use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Domain\Model\Currency;
use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class CartQueryControllerTest extends WebTestCase
{
    private DoctrineCartRepository $repository;
    private AddProductToCartHandler $addHandler;
    private EntityManagerInterface $em;
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

    protected function setUp(): void
    {
        echo "🧪 Running functional test: GET /carts (list all carts)\n";

        $this->client = static::createClient();

        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $this->repository = new DoctrineCartRepository($this->em);
        $this->addHandler = new AddProductToCartHandler($this->repository);
    }

    public function test_it_returns_all_carts(): void
    {
        $cart1Id = 'cart-1';
        $product1 = Uuid::v4()->toRfc4122();
        $currency = new Currency('EUR');

        // Add product to cart-1
        ($this->addHandler)(new AddProductToCartCommand(
            $cart1Id,
            $product1,
            2,
            1000,
            $currency
        ));

        $cart2Id = 'cart-2';
        $product2 = Uuid::v4()->toRfc4122();

        // Add product to cart-2
        ($this->addHandler)(new AddProductToCartCommand(
            $cart2Id,
            $product2,
            1,
            2500,
            $currency
        ));

        $this->client->request('GET', '/carts');

        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $data);
        $this->assertEquals($cart1Id, $data[0]['id']);
        $this->assertEquals($cart2Id, $data[1]['id']);
    }
}
