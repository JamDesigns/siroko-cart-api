<?php

namespace App\Tests\Checkout\Infrastructure\Persistence\Doctrine;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\Currency;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Quantity;
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

        // ✅ Use isolated repository, not from container
        $cartRepo = new InMemoryCartRepository();
        $orderRepo = static::getContainer()->get(DoctrineOrderRepository::class);

        $cartId = 'checkout-db-cart-isolated';
        $product = new Product('66666666-6666-6666-6666-666666666666');
        $currency = new Currency('EUR');

        $cart = new Cart($cartId);
        $cart->addProduct($product, new Quantity(2), new Money(1000, $currency));
        $cartRepo->save($cart);

        $handler = new CheckoutCartHandler($cartRepo, $orderRepo);
        $handler(new CheckoutCartCommand($cartId));

        // 🔍 Verify persisted order
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

        // 🧹 Clean DB after test
        $this->em->createQuery('DELETE FROM App\Checkout\Infrastructure\Persistence\Doctrine\OrderItemEntity')->execute();
        $this->em->createQuery('DELETE FROM App\Checkout\Infrastructure\Persistence\Doctrine\OrderEntity')->execute();
    }
}
