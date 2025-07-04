<?php

namespace App\Tests\Cart\Infrastructure\Controller;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\Currency;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\ProductId;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UpdateProductQuantityControllerTest extends WebTestCase
{
    public function test_it_updates_quantity_of_existing_product(): void
    {
        echo "🧪 Running functional test: PATCH /cart/{id}/items/{productId}\n";

        $client = static::createClient();
        $container = static::getContainer();

        /** @var InMemoryCartRepository $repo */
        $repo = $container->get(InMemoryCartRepository::class);

        $cartId = 'test-cart-update';
        $productId = new ProductId('22222222-2222-2222-2222-222222222222');
        $currency = new Currency('EUR');

        // Create cart and add product
        $cart = new Cart($cartId);
        $cart->addProduct($productId, new Quantity(1), new Money(1000, $currency));
        $repo->save($cart);

        $payload = ['quantity' => 5];

        $client->request(
            'PATCH',
            "/cart/{$cartId}/items/{$productId->value()}",
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
}
