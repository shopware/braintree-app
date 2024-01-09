<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Entity\Contract;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Entity\Contract\EntityDateTrait;
use Swag\Braintree\Entity\Contract\EntityIdTrait;
use Swag\Braintree\Entity\Contract\EntityTrait;
use Symfony\Component\Uid\Uuid;

#[CoversClass(EntityTrait::class)]
#[CoversClass(EntityIdTrait::class)]
#[CoversClass(EntityDateTrait::class)]
class EntityTraitTest extends TestCase
{
    public function testTrait(): void
    {
        $entity = new class() {
            use EntityTrait;
        };

        static::assertTrue(\property_exists($entity, 'id'));
        static::assertTrue(\property_exists($entity, 'createdAt'));
        static::assertTrue(\property_exists($entity, 'updatedAt'));

        static::assertTrue(\method_exists($entity, 'getId'));
        static::assertTrue(\method_exists($entity, 'setId'));
        static::assertTrue(\method_exists($entity, 'getCreatedAt'));
        static::assertTrue(\method_exists($entity, 'setCreatedAt'));
        static::assertTrue(\method_exists($entity, 'getUpdatedAt'));
        static::assertTrue(\method_exists($entity, 'setUpdatedAt'));
        static::assertTrue(\method_exists($entity, 'onPrePersist'));
        static::assertTrue(\method_exists($entity, 'onPreUpdate'));

        static::assertNull($entity->getId());
        static::assertNull($entity->getUpdatedAt());

        $id = Uuid::v7();
        $date = new \DateTime();

        $entity->setId($id);
        $entity->setCreatedAt($date);
        $entity->setUpdatedAt($date);

        static::assertSame($id, $entity->getId());
        static::assertSame($date, $entity->getCreatedAt());
        static::assertSame($date, $entity->getUpdatedAt());
    }

    public function testDoctrineLifecycleMethods(): void
    {
        $entity = new class() {
            use EntityTrait;
        };

        static::assertNull($entity->getUpdatedAt());

        $entity->onPrePersist();
        $entity->onPreUpdate();

        static::assertInstanceOf(\DateTime::class, $entity->getCreatedAt());
        static::assertInstanceOf(\DateTime::class, $entity->getUpdatedAt());
    }
}
