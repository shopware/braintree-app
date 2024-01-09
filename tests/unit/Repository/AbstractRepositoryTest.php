<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Entity\Contract\EntityTrait;
use Swag\Braintree\Entity\Contract\SalesChannelAwareTrait;
use Swag\Braintree\Entity\Contract\ShopAwareTrait;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Repository\AbstractRepository;
use Swag\Braintree\Tests\Entity;
use Swag\Braintree\Tests\IdsCollection;
use Swag\Braintree\Tests\Repository;
use Swag\Braintree\Tests\Serializer\TestSerializer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

#[CoversClass(AbstractRepository::class)]
class AbstractRepositoryTest extends TestCase
{
    private IdsCollection $ids;

    private MockObject&EntityManagerInterface $entityManager;

    private MockObject&ManagerRegistry $registry;

    private Repository $repository;

    protected function setUp(): void
    {
        $this->ids = new IdsCollection();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry
            ->method('getManagerForClass')
            ->willReturn($this->entityManager);

        $this->repository = new Repository($this->registry, Entity::class);
    }

    public function testUpsert(): void
    {
        $upsertData = [
            ['id' => $this->ids->get('foo')],
            ['id' => $this->ids->get('bar')],
        ];

        $entity = new Entity();

        $this->entityManager
            ->method('getClassMetadata')
            ->willReturn(new ClassMetadata(Entity::class));

        $this->entityManager
            ->expects(static::exactly(2))
            ->method('getReference')
            ->with(Entity::class, static::anything())
            ->willReturn($entity);

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects(static::exactly(2))
            ->method('denormalize')
            ->willReturn($entity);

        $this->repository->setDenormalizer($denormalizer);

        $this->entityManager
            ->expects(static::exactly(2))
            ->method('persist')
            ->with($entity);

        $this->entityManager
            ->expects(static::once())
            ->method('flush');

        $this->repository->upsert($upsertData, new ShopEntity('', '', ''));
    }

    public function testUpsertWithNewEntity(): void
    {
        $upsertData = [[]];

        $this->entityManager
            ->method('getClassMetadata')
            ->willReturn(new ClassMetadata(Entity::class));

        $this->entityManager
            ->expects(static::never())
            ->method('getReference');

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with(static::callback(static function (Entity $persist) {
                static::assertInstanceOf(Entity::class, $persist);
                static::assertNull($persist->getId());

                return true;
            }));

        $this->entityManager
            ->expects(static::once())
            ->method('flush');

        $this->repository->setDenormalizer(TestSerializer::create());
        $this->repository->upsert($upsertData, new ShopEntity('', '', ''));
    }

    public function testUpsertWithSalesChannelAwareNull(): void
    {
        $entity = new class() extends Entity {
            use SalesChannelAwareTrait;
        };

        $entity->setSalesChannelId('foo');

        $id = (string) Uuid::v7();

        $upsertData = [['id' => $id, 'salesChannelId' => 'null']];

        $this->entityManager
            ->method('getClassMetadata')
            ->willReturn(new ClassMetadata($entity::class));

        $this->entityManager
            ->expects(static::once())
            ->method('getReference')
            ->with($entity::class, $id)
            ->willReturn($entity);

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with(static::callback(static function (Entity $persist) {
                static::assertTrue(\method_exists($persist, 'getSalesChannelId'));
                static::assertNull($persist->getSalesChannelId());

                return true;
            }));

        $repository = new Repository($this->registry, $entity::class);
        $repository->setDenormalizer(TestSerializer::create());
        $repository->upsert($upsertData, new ShopEntity('', '', ''));
    }

    public function testUpsertWithSalesChannelAwareMissing(): void
    {
        $entity = new class() extends Entity {
            use SalesChannelAwareTrait;
        };

        $prop = new \ReflectionProperty($entity, 'salesChannelId');

        $id = (string) Uuid::v7();

        $upsertData = [['id' => $id]];

        $this->entityManager
            ->method('getClassMetadata')
            ->willReturn(new ClassMetadata($entity::class));

        $this->entityManager
            ->expects(static::once())
            ->method('getReference')
            ->with($entity::class, $id)
            ->willReturn($entity);

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with(static::callback(static function (Entity $persist) use ($prop) {
                static::assertFalse($prop->isInitialized($persist));

                return true;
            }));

        $repository = new Repository($this->registry, $entity::class);
        $repository->setDenormalizer(TestSerializer::create());
        $repository->upsert($upsertData, new ShopEntity('', '', ''));
    }

    public function testUpsertWithShopAware(): void
    {
        $entity = new class() extends Entity {
            use ShopAwareTrait;
        };

        $entity->setShop(new ShopEntity('', '', ''));

        $id = (string) Uuid::v7();

        $upsertData = [['id' => $id]];

        $this->entityManager
            ->method('getClassMetadata')
            ->willReturn(new ClassMetadata($entity::class));

        $this->entityManager
            ->expects(static::once())
            ->method('getReference')
            ->with($entity::class, $id)
            ->willReturn($entity);

        $shop = new ShopEntity('', '', '');

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with(static::callback(static function (Entity $persist) use ($shop) {
                static::assertTrue(\method_exists($persist, 'getShop'));
                static::assertSame($shop, $persist->getShop());

                return true;
            }));

        $repository = new Repository($this->registry, $entity::class);
        $repository->setDenormalizer(TestSerializer::create());
        $repository->upsert($upsertData, $shop);
    }

    public function testDelete(): void
    {
        $id = (string) Uuid::v7();

        $this->entityManager
            ->method('getClassMetadata')
            ->willReturn(new ClassMetadata(Entity::class));

        $this->entityManager
            ->expects(static::once())
            ->method('remove');

        $this->entityManager
            ->expects(static::once())
            ->method('getReference')
            ->with(Entity::class, $id)
            ->willReturn(new Entity());

        $this->repository->delete([$id]);

        static::assertTrue(true);
    }

    public function testDeserializeInto(): void
    {
        $data = '{"name": "John Doe"}';
        $entity = new Entity();

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects(static::once())
            ->method('deserialize')
            ->with(
                $data,
                Entity::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $entity,
                    AbstractNormalizer::GROUPS => ['admin-write'],
                ]
            )
            ->willReturn($entity);

        $this->repository->setSerializer($serializer);

        $this->entityManager
            ->method('getClassMetadata')
            ->willReturn(new ClassMetadata(Entity::class));

        $result = $this->repository->deserializeInto($entity, $data);

        static::assertSame($entity, $result);
    }

    public function testDenormalizeInto(): void
    {
        $data = [];
        $entity = new Entity();

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects(static::once())
            ->method('denormalize')
            ->with(
                $data,
                Entity::class,
                'array',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $entity,
                    AbstractNormalizer::GROUPS => ['admin-write'],
                ]
            )
            ->willReturn($entity);

        $this->repository->setDenormalizer($denormalizer);

        $this->entityManager
            ->method('getClassMetadata')
            ->willReturn(new ClassMetadata(Entity::class));

        $result = $this->repository->denormalizeInto($entity, $data);

        static::assertSame($entity, $result);
    }

    public function testDenormalizerNotInstantiated(): void
    {
        $this->entityManager
            ->method('getClassMetadata')
            ->willReturn(new ClassMetadata(Entity::class));

        $denormalizer = new \ReflectionProperty($this->repository::class, 'denormalizer');

        static::assertFalse($denormalizer->isInitialized($this->repository));

        $this->repository->setDenormalizer($this->createMock(DenormalizerInterface::class));
        static::assertTrue($denormalizer->isInitialized($this->repository));
    }

    public function testSerializerNotInstantiated(): void
    {
        $this->entityManager
            ->method('getClassMetadata')
            ->willReturn(new ClassMetadata(Entity::class));

        $serializer = new \ReflectionProperty($this->repository::class, 'serializer');

        static::assertFalse($serializer->isInitialized($this->repository));

        $this->repository->setSerializer($this->createMock(SerializerInterface::class));
        static::assertTrue($serializer->isInitialized($this->repository));
    }

    public function testHasTraitWithDefaultTraits(): void
    {
        $this->entityManager
            ->method('getClassMetadata')
            ->willReturn(new ClassMetadata(Entity::class));

        $method = (new \ReflectionClass($this->repository::class))
            ->getMethod('hasTrait');

        static::assertTrue($method->isPrivate());
        static::assertTrue($method->invokeArgs($this->repository, [EntityTrait::class]));
        static::assertFalse($method->invokeArgs($this->repository, [SalesChannelAwareTrait::class]));
        static::assertFalse($method->invokeArgs($this->repository, [ShopAwareTrait::class]));
        static::assertFalse($method->invokeArgs($this->repository, ['foo']));
    }

    public function testHasTraitWithoutTraits(): void
    {
        $this->entityManager
            ->method('getClassMetadata')
            ->willReturn(new ClassMetadata(\stdClass::class));

        $method = (new \ReflectionClass($this->repository::class))->getMethod('hasTrait');

        static::assertFalse($method->invokeArgs($this->repository, [EntityTrait::class]));
    }
}
