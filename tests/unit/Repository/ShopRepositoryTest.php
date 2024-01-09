<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Repository\ShopRepository;
use Swag\Braintree\Tests\Serializer\TestSerializer;

#[CoversClass(ShopRepository::class)]
class ShopRepositoryTest extends TestCase
{
    private MockObject&EntityManagerInterface $entityManager;

    private MockObject&ManagerRegistry $registry;

    private ShopRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager
            ->expects(static::once())
            ->method('getClassMetadata')
            ->with(ShopEntity::class)
            ->willReturn(new ClassMetadata(ShopEntity::class));

        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry
            ->expects(static::once())
            ->method('getManagerForClass')
            ->with(ShopEntity::class)
            ->willReturn($this->entityManager);

        $serializer = TestSerializer::create();

        $this->repository = new ShopRepository($this->registry);
        $this->repository->setDenormalizer($serializer);
    }

    public function testConstruct(): void
    {
        $this->entityManager->expects(static::once())->method('getClassMetadata');
        $this->registry->expects(static::once())->method('getManagerForClass');

        static::assertSame(ShopEntity::class, $this->repository->getClassName());
    }

    public function testUpsert(): void
    {
        $shop = (new ShopEntity('this-is-id', 'this-is-url', 'this-is-secret'))
            ->setBraintreeMerchantId('this-is-merchant-id')
            ->setBraintreePublicKey('this-is-public-key')
            ->setBraintreePrivateKey('this-is-private-key');

        $data = [
            'shopId' => 'this-is-id-override',
            'shopUrl' => 'this-is-url-override',
            'shopSecret' => 'this-is-secret-override',
            'braintreeMerchantId' => 'this-is-merchant-id-override',
            'braintreePublicKey' => 'this-is-public-key-override',
            'braintreePrivateKey' => 'this-is-private-key-override',
        ];

        $this->entityManager
            ->expects(static::once())
            ->method('flush');

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with(static::callback(static function (ShopEntity $entity) use ($shop, $data) {
                static::assertSame($shop, $entity);
                static::assertSame('this-is-id', $entity->getShopId());
                static::assertSame('this-is-url', $entity->getShopUrl());
                static::assertSame('this-is-secret', $entity->getShopSecret());
                static::assertSame($data['braintreeMerchantId'], $entity->getBraintreeMerchantId());
                static::assertSame($data['braintreePublicKey'], $entity->getBraintreePublicKey());
                static::assertSame($data['braintreePrivateKey'], $entity->getBraintreePrivateKey());

                return true;
            }));

        $this->repository->upsert($data, $shop);
    }
}
