<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Entity\Contract;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Entity\Contract\ShopAwareTrait;
use Swag\Braintree\Entity\ShopEntity;

#[CoversClass(ShopAwareTrait::class)]
class ShopAwareTraitTest extends TestCase
{
    public function testTrait(): void
    {
        $entity = new class() {
            use ShopAwareTrait;
        };

        static::assertTrue(\property_exists($entity, 'shop'));

        static::assertTrue(\method_exists($entity, 'getShop'));
        static::assertTrue(\method_exists($entity, 'setShop'));

        $reflection = new \ReflectionProperty($entity::class, 'shop');
        static::assertFalse($reflection->isInitialized($entity));

        $shop = new ShopEntity('shop-id', '', 'secret');

        $entity->setShop($shop);
        static::assertSame($shop, $entity->getShop());
    }
}
