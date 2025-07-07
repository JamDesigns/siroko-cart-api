<?php

namespace App\Tests\Cart\Infrastructure\Controller;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Model\Money;
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

        $repo = $container->get(DoctrineCartRepository::class);

        $cartId = Uuid::v4()->toRfc4122();
        $product = new Product(Uuid::v4()->toRfc4122());

        $cart = new Cart($cartId);
        $cart->addProduct($product, new Quantity(1), new Money(1000, new Currency('EUR')));

        $repo->save($cart);

        $this->assertNotNull($repo->find($cartId));

        $client->request('DELETE', "/cart/{$cartId}");
        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Cart deleted', $response['message']);

        $this->assertNull($repo->find($cartId));
    }
}
