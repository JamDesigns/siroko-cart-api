<?php

namespace App\Tests\Checkout\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use App\Checkout\Infrastructure\Persistence\Doctrine\OrderEntity;
use App\Checkout\Infrastructure\Persistence\Doctrine\OrderItemEntity;

class OrderQueryControllerTest extends WebTestCase
{
    private EntityManagerInterface $em;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        echo "🧪 Running functional test: GET /orders (list all orders)\n";

        $this->client = static::createClient(); // Inicializa kernel y cliente
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());

        // Seed data
        $order1 = new OrderEntity('order-1', 3000, 'EUR');
        $order1->addItem(new OrderItemEntity(Uuid::v4()->toRfc4122(), 1, 3000));

        $order2 = new OrderEntity('order-2', 5000, 'EUR');
        $order2->addItem(new OrderItemEntity(Uuid::v4()->toRfc4122(), 2, 2500));

        $this->em->persist($order1);
        $this->em->persist($order2);
        $this->em->flush();
    }

    public function test_it_returns_all_orders(): void
    {
        $this->client->request('GET', '/orders');

        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $data);
        $this->assertEquals('order-1', $data[0]['id']);
        $this->assertEquals('order-2', $data[1]['id']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->createQuery('DELETE FROM App\Checkout\Infrastructure\Persistence\Doctrine\OrderItemEntity')->execute();
        $this->em->createQuery('DELETE FROM App\Checkout\Infrastructure\Persistence\Doctrine\OrderEntity')->execute();
    }
}
