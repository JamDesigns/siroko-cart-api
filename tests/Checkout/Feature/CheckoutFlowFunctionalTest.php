<?php

namespace App\Tests\Checkout\Feature;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Uid\Uuid;

class CheckoutFlowFunctionalTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        echo "🧪 Running functional flow test: Checkout flow with DB\n";

        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        // 🧼 Reset DB schema
        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function test_checkout_process_persists_order(): void
    {
        $cartId = 'checkout-functional-cart';
        $product = Uuid::v4()->toRfc4122();

        // ➕ Add product to cart
        $this->client->request(
            'POST',
            "/cart/{$cartId}/items",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => $product,
                'quantity' => 2,
                'unitPrice' => 1200,
                'currency' => 'EUR',
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        // 🧾 Perform checkout
        $this->client->request('POST', "/checkout/{$cartId}");

        $this->assertResponseStatusCodeSame(201);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Order created successfully', $response['message']);

        // ✅ Verify in DB
        $order = $this->em
            ->getRepository(\App\Checkout\Infrastructure\Persistence\Doctrine\OrderEntity::class)
            ->findOneBy(['id' => $cartId]);

        $this->assertNotNull($order);
        $this->assertEquals(2400, $order->total());
        $this->assertEquals('EUR', $order->currency());
        $this->assertCount(1, $order->items());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->createQuery('DELETE FROM App\Checkout\Infrastructure\Persistence\Doctrine\OrderItemEntity')->execute();
        $this->em->createQuery('DELETE FROM App\Checkout\Infrastructure\Persistence\Doctrine\OrderEntity')->execute();
    }
}
