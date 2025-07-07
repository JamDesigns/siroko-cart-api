<?php

namespace App\Tests\Cart\Application\Command;

use App\Cart\Application\Command\AddProductToCartCommand;
use App\Cart\Application\Command\AddProductToCartHandler;
use App\Cart\Domain\Model\Cart;
use App\Cart\Infrastructure\Persistence\Doctrine\CartEntity;
use App\Cart\Domain\Model\Product;
use App\Cart\Domain\Model\Quantity;
use App\Cart\Domain\Model\Money;
use App\Cart\Domain\Model\Currency;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class AddProductToCartHandlerTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private AddProductToCartHandler $handler;

    protected function setUp(): void
    {
        echo "🧪 Running application test: AddProductToCartHandler\n";

        self::bootKernel();

        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->handler = static::getContainer()->get(AddProductToCartHandler::class);

        // 🔄 Reset SQLite schema
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function test_it_adds_product_to_cart_and_persists_it(): void
    {
        $cartId = Uuid::v4()->toRfc4122();
        $product = Uuid::v4()->toRfc4122();
        $quantity = 2;
        $price = 1500;
        $currency = new Currency('EUR');

        $command = new AddProductToCartCommand(
            $cartId,
            $product,
            $quantity,
            $price,
            $currency
        );

        ($this->handler)($command);

        $cartEntity = $this->em->getRepository(CartEntity::class)->findOneBy(['id' => $cartId]);

        $this->assertNotNull($cartEntity);
        $this->assertSame($cartId, $cartEntity->getId());
        $this->assertCount(1, $cartEntity->getItems());

        $item = $cartEntity->getItems()[0];
        $this->assertSame($product, $item->getProductId());
        $this->assertSame($quantity, $item->getQuantity());
        $this->assertSame($price, $item->getUnitPrice());
        $this->assertSame($currency->code(), $item->getCurrency());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartItemEntity')->execute();
        $this->em->createQuery('DELETE FROM App\Cart\Infrastructure\Persistence\Doctrine\CartEntity')->execute();
    }
}
