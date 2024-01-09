<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Entity\ConfigEntity;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Framework\Exception\EntityPropertyRequiredException;
use Swag\Braintree\Repository\ConfigRepository;
use Swag\Braintree\Tests\Serializer\TestSerializer;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ConfigRepository::class)]
class ConfigRepositoryTest extends TestCase
{
    private MockObject&EntityManagerInterface $entityManager;

    private MockObject&ManagerRegistry $registry;

    private ConfigRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager
            ->method('getClassMetadata')
            ->with(ConfigEntity::class)
            ->willReturn(new ClassMetadata(ConfigEntity::class));

        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry
            ->method('getManagerForClass')
            ->with(ConfigEntity::class)
            ->willReturn($this->entityManager);

        $serializer = TestSerializer::create();

        $this->repository = new ConfigRepository($this->registry);
        $this->repository->setDenormalizer($serializer);
    }

    public function testConstruct(): void
    {
        $this->entityManager->expects(static::once())->method('getClassMetadata');
        $this->registry->expects(static::once())->method('getManagerForClass');

        static::assertSame(ConfigEntity::class, $this->repository->getClassName());
    }

    public function testUpsertWithNewEntity(): void
    {
        $shop = new ShopEntity('', '', '');
        $data = [
            [
                'id' => null,
                'salesChannelId' => 'null',
                'threeDSecureEnforced' => true,
            ],
        ];

        $this->entityManager
            ->expects(static::never())
            ->method('getReference');

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with(static::callback(static function (ConfigEntity $entity) use ($shop) {
                static::assertNull($entity->getId());
                static::assertNull($entity->getSalesChannelId());
                static::assertTrue($entity->isThreeDSecureEnforced());
                static::assertSame($shop, $entity->getShop());

                return true;
            }));

        $this->entityManager
            ->expects(static::once())
            ->method('flush');

        $this->repository->upsert($data, $shop);
    }

    public function testUpsertWithExistingEntity(): void
    {
        $id = Uuid::v7();
        $shop = new ShopEntity('', '', '');

        $config = (new ConfigEntity())
            ->setSalesChannelId('this-is-sales-channel-id')
            ->setId($id);

        $data = [
            'id' => (string) $id,
            'salesChannelId' => 'this-is-sales-channel-id-override',
            'threeDSecureEnforced' => null,
        ];

        $this->entityManager
            ->expects(static::once())
            ->method('getReference')
            ->with(ConfigEntity::class, $id)
            ->willReturn($config);

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with(static::callback(static function (ConfigEntity $entity) use ($shop, $config, $data) {
                static::assertSame($config, $entity);
                static::assertSame($data['id'], (string) $entity->getId());
                static::assertSame($data['salesChannelId'], $entity->getSalesChannelId());
                static::assertNull($entity->isThreeDSecureEnforced());
                static::assertSame($shop, $entity->getShop());

                return true;
            }));

        $this->repository->upsert([$data], $shop);
    }

    public function testUpsertWithoutThreeDSecureEnforcedThrowsException(): void
    {
        $this->expectException(EntityPropertyRequiredException::class);
        $this->expectExceptionMessage('"threeDSecureEnforced" is required to be set');

        $this->repository->upsert(
            [['salesChannelId' => null]],
            new ShopEntity('', '', '')
        );
    }

    public function testUpsertThreeDSecureEnforcedNullThrowsException(): void
    {
        $this->expectException(EntityPropertyRequiredException::class);
        $this->expectExceptionMessage('"threeDSecureEnforced" is required to be set');

        $this->repository->upsert(
            [['salesChannelId' => null, 'threeDSecureEnforced' => null]],
            new ShopEntity('', '', '')
        );
    }

    public function testUpsertWithEmptyArray(): void
    {
        $this->entityManager
            ->expects(static::never())
            ->method('persist');

        $this->repository->upsert([], new ShopEntity('', '', ''));
    }
}
