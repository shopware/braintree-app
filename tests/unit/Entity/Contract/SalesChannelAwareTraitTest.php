<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Entity\Contract;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Entity\Contract\SalesChannelAwareTrait;

#[CoversClass(SalesChannelAwareTrait::class)]
class SalesChannelAwareTraitTest extends TestCase
{
    public function testTrait(): void
    {
        $entity = new class() {
            use SalesChannelAwareTrait;
        };

        static::assertTrue(\property_exists($entity, 'salesChannelId'));

        static::assertTrue(\method_exists($entity, 'getSalesChannelId'));
        static::assertTrue(\method_exists($entity, 'setSalesChannelId'));

        $reflection = new \ReflectionProperty($entity::class, 'salesChannelId');
        static::assertFalse($reflection->isInitialized($entity));

        $entity->setSalesChannelId('this-is-sales-channel-id');
        static::assertSame('this-is-sales-channel-id', $entity->getSalesChannelId());
    }
}
