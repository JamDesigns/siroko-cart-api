<?php

namespace App\Tests\Cart\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AddProductToCartControllerTest extends WebTestCase
{
    public function test_it_adds_product_to_cart(): void
    {
        echo "🧪 Running functional test: POST /cart/{id}/items\n";

        $client = static::createClient();

        $cartId = 'test-cart-api-add';
        $payload = [
            'productId' => '11111111-1111-1111-1111-111111111111',
            'quantity' => 3,
            'unitPrice' => 2000, // 20 €
            'currency' => 'EUR'
        ];

        $client->request(
            'POST',
            "/cart/{$cartId}/items",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Product added to cart', $data['message'] ?? null);
    }
}
