<?php

namespace App\Tests\Cart\Infrastructure\Controller;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\Currency;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Tools\SchemaTool;

class CartQueryControllerTest extends WebTestCase
{
    private DoctrineCartRepository $repository;
    private EntityManagerInterface $em;
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

    protected function setUp(): void
    {
        echo "🧪 Running functional test: GET /carts (list all carts)\n";

        $this->client = static::createClient(); // ✅ Solo una vez

        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());

        $this->repository = new DoctrineCartRepository($this->em);
    }

    public function test_it_returns_all_carts(): void
    {
        $cart1 = new Cart('cart-1');
        $cart1->addProduct(
            new Product(Uuid::v4()->toRfc4122()),
            new Quantity(2),
            new Money(1000, new Currency('EUR'))
        );

        $cart2 = new Cart('cart-2');
        $cart2->addProduct(
            new Product(Uuid::v4()->toRfc4122()),
            new Quantity(1),
            new Money(2500, new Currency('EUR'))
        );

        $this->repository->save($cart1);
        $this->repository->save($cart2);

        // Usar client ya creado en setUp()
        $this->client->request('GET', '/carts');

        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $data);
        $this->assertEquals('cart-1', $data[0]['id']);
        $this->assertEquals('cart-2', $data[1]['id']);
    }
}
