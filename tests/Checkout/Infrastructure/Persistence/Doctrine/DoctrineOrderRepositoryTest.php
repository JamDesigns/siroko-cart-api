<?php

namespace App\Tests\Checkout\Infrastructure\Persistence\Doctrine;

use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Domain\Model\Currency;
use App\Cart\Domain\Model\Product;
use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use App\Checkout\Application\Command\CheckoutCartCommand;
use App\Checkout\Application\Command\CheckoutCartHandler;
use App\Checkout\Infrastructure\Persistence\Doctrine\OrderEntity;
use App\Checkout\Infrastructure\Persistence\Doctrine\DoctrineOrderRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineOrderRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function test_it_persists_order_in_database(): void
    {
        echo "🧪 Running DB persistence test: DoctrineOrderRepository\n";

        $cartRepo = new InMemoryCartRepository();
        $orderRepo = static::getContainer()->get(DoctrineOrderRepository::class);

        $cartId = 'checkout-db-cart-isolated';
        $product = new Product('66666666-6666-6666-6666-666666666666');
        $currency = new Currency('EUR');

        $addHandler = new AddProductToCartHandler($cartRepo);
        ($addHandler)(new AddProductToCartCommand(
            $cartId,
            $product->value(),
            2,
            1000,
            $currency
        ));

        $handler = new CheckoutCartHandler($cartRepo, $orderRepo);
        $handler(new CheckoutCartCommand($cartId));

        $order = $this->em->getRepository(OrderEntity::class)->findOneBy(['id' => $cartId]);
        if (!$order) {
            $order = $this->em->getRepository(OrderEntity::class)->findOneBy([], ['id' => 'DESC']);
        }

        $this->assertNotNull($order);
        $this->assertEquals(2000, $order->total());
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
