<?php

namespace App\Tests\Cart\Infrastructure\Persistence\Doctrine;

use App\Cart\Domain\Model\Cart;
use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\Currency;
use App\Cart\Infrastructure\Persistence\Doctrine\DoctrineCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class DoctrineCartRepositoryTest extends KernelTestCase
{
    private DoctrineCartRepository $repository;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        echo "🧪 Running DB persistence test: DoctrineCartRepository\n";

        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        // Reset in-memory schema
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $this->repository = new DoctrineCartRepository($this->em);
    }

    public function testCartIsPersistedAndRestored(): void
    {
        $cartId = Uuid::v4()->toRfc4122();
        $cart = new Cart($cartId);

        $product = new Product(Uuid::v4()->toRfc4122());
        $quantity = new Quantity(2);
        $money = new Money(1200, new Currency('EUR'));

        $cart->addProduct($product, $quantity, $money);
        $this->repository->save($cart);

        $restored = $this->repository->find($cartId);

        $this->assertNotNull($restored, 'Cart should be found after being saved.');
        $this->assertSame($cartId, $restored->id());
        $this->assertCount(1, $restored->items());

        $item = $restored->items()[0];
        $this->assertSame($product->toPrimitives(), $item->product()->toPrimitives());
        $this->assertSame($quantity->toPrimitives(), $item->quantity()->toPrimitives());
        $this->assertSame($money->amount(), $item->unitPrice()->amount());
        $this->assertSame($money->currency()->code(), $item->unitPrice()->currency()->code());
    }

    public function testFindReturnsCartWhenItExists(): void
    {
        $cartId = Uuid::v4()->toRfc4122();
        $cart = new Cart($cartId);

        $cart->addProduct(new Product(Uuid::v4()->toRfc4122()), new Quantity(1), new Money(500, new Currency('EUR')));
        $this->repository->save($cart);

        $result = $this->repository->find($cartId);

        $this->assertInstanceOf(Cart::class, $result);
        $this->assertSame($cartId, $result->id());
    }

    public function testFindReturnsNullWhenCartDoesNotExist(): void
    {
        $cartId = Uuid::v4()->toRfc4122();

        $result = $this->repository->find($cartId);

        $this->assertNull($result, 'Expected null when cart is not found.');
    }

    public function testCartIsDeleted(): void
    {
        $cartId = Uuid::v4()->toRfc4122();
        $cart = new Cart($cartId);

        $cart->addProduct(new Product(Uuid::v4()->toRfc4122()), new Quantity(1), new Money(500, new Currency('EUR')));
        $this->repository->save($cart);

        $this->assertNotNull($this->repository->find($cartId));

        $this->repository->delete($cartId);

        $this->assertNull($this->repository->find($cartId), 'Cart should be null after deletion.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // 🧹 Optional: clean DB manually (not needed with drop/create schema per test)
        $this->em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartItemEntity')->execute();
        $this->em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartEntity')->execute();
    }
}
