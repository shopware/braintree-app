<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Entity\CurrencyMappingEntity;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Repository\CurrencyMappingRepository;
use Swag\Braintree\Tests\Serializer\TestSerializer;
use Symfony\Component\Uid\Uuid;

#[CoversClass(CurrencyMappingRepository::class)]
class CurrencyMappingRepositoryTest extends TestCase
{
    private MockObject&EntityManagerInterface $entityManager;

    private MockObject&ManagerRegistry $registry;

    private CurrencyMappingRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager
            ->method('getClassMetadata')
            ->with(CurrencyMappingEntity::class)
            ->willReturn(new ClassMetadata(CurrencyMappingEntity::class));

        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry
            ->method('getManagerForClass')
            ->with(CurrencyMappingEntity::class)
            ->willReturn($this->entityManager);

        $serializer = TestSerializer::create();

        $this->repository = new CurrencyMappingRepository($this->registry);
        $this->repository->setDenormalizer($serializer);
    }

    public function testConstruct(): void
    {
        $this->entityManager->expects(static::once())->method('getClassMetadata');
        $this->registry->expects(static::once())->method('getManagerForClass');

        static::assertSame(CurrencyMappingEntity::class, $this->repository->getClassName());
    }

    public function testUpsertWithNewEntity(): void
    {
        $shop = new ShopEntity('', '', '');
        $data = [
            'id' => null,
            'salesChannelId' => 'null',
            'currencyId' => 'this-is-currency-id',
            'currencyIso' => 'this-is-currency-iso',
            'merchantAccountId' => 'this-is-merchant-account-id',
        ];

        $this->entityManager
            ->expects(static::never())
            ->method('getReference');

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with(static::callback(static function (CurrencyMappingEntity $entity) use ($shop, $data) {
                static::assertNull($entity->getId());
                static::assertNull($entity->getSalesChannelId());
                static::assertSame($data['currencyId'], $entity->getCurrencyId());
                static::assertSame($data['currencyIso'], $entity->getCurrencyIso());
                static::assertSame($data['merchantAccountId'], $entity->getMerchantAccountId());
                static::assertSame($shop, $entity->getShop());

                return true;
            }));

        $this->repository->upsert([$data], $shop);
    }

    public function testUpsertWithExistingEntity(): void
    {
        $id = Uuid::v7();
        $shop = new ShopEntity('', '', '');

        $currencyMapping = (new CurrencyMappingEntity())
            ->setSalesChannelId('this-is-sales-channel-id')
            ->setCurrencyId('this-is-currency-id')
            ->setCurrencyIso('this-is-currency-iso')
            ->setMerchantAccountId('this-is-merchant-account-id')
            ->setId($id);

        $data = [
            'id' => (string) $id,
            'salesChannelId' => 'this-is-sales-channel-id-override',
            'currencyId' => 'this-is-currency-id-override',
            'currencyIso' => 'this-is-currency-iso-override',
            'merchantAccountId' => 'this-is-merchant-account-id-override',
        ];

        $this->entityManager
            ->expects(static::once())
            ->method('getReference')
            ->with(CurrencyMappingEntity::class, $id)
            ->willReturn($currencyMapping);

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with(static::callback(static function (CurrencyMappingEntity $entity) use ($shop, $currencyMapping, $data) {
                static::assertSame($currencyMapping, $entity);
                static::assertSame($data['id'], (string) $entity->getId());
                static::assertSame($data['salesChannelId'], $entity->getSalesChannelId());
                static::assertSame($data['currencyId'], $entity->getCurrencyId());
                static::assertSame($data['currencyIso'], $entity->getCurrencyIso());
                static::assertSame($data['merchantAccountId'], $entity->getMerchantAccountId());
                static::assertSame($shop, $entity->getShop());

                return true;
            }));

        $this->repository->upsert([$data], $shop);
    }

    public function testUpsertWithEmptyArray(): void
    {
        $this->entityManager
            ->expects(static::never())
            ->method('persist');

        $this->repository->upsert([], new ShopEntity('', '', ''));
    }
}
