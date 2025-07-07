<?php

namespace App\Tests\Cart\Feature;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class CartFlowFunctionalTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        echo "🧪 Running functional flow test: Cart Add → Get → Delete\n";

        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function test_cart_flow_with_persistence(): void
    {
        $cartId = Uuid::v4()->toRfc4122();
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
                'unitPrice' => 1000,
                'currency' => 'EUR'
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        // 🔍 Get cart and assert product is there
        $this->client->request('GET', "/cart/{$cartId}");

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($cartId, $responseData['id']);
        $this->assertEquals(2000, $responseData['total']);
        $this->assertEquals('EUR', $responseData['currency']);
        $this->assertCount(1, $responseData['items']);
        $this->assertEquals($product, $responseData['items'][0]['product']);

        // 🗑️ Remove product
        $this->client->request('DELETE', "/cart/{$cartId}/items/{$product}");
        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Product removed', $response['message']);

        // ✅ Confirm cart is now empty
        $this->client->request('GET', "/cart/{$cartId}");
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(0, $data['items']);
        $this->assertEquals(0, $data['total']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartItemEntity')->execute();
        $this->em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartEntity')->execute();
    }
}
