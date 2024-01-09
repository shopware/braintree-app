<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Entity\TransactionEntity;
use Swag\Braintree\Repository\TransactionRepository;
use Swag\Braintree\Tests\Serializer\TestSerializer;
use Symfony\Component\Uid\Uuid;

#[CoversClass(TransactionRepository::class)]
class TransactionRepositoryTest extends TestCase
{
    private MockObject&EntityManagerInterface $entityManager;

    private MockObject&ManagerRegistry $registry;

    private TransactionRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager
            ->method('getClassMetadata')
            ->with(TransactionEntity::class)
            ->willReturn(new ClassMetadata(TransactionEntity::class));

        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry
            ->method('getManagerForClass')
            ->with(TransactionEntity::class)
            ->willReturn($this->entityManager);

        $this->repository = new TransactionRepository($this->registry);
        $this->repository->setDenormalizer(TestSerializer::create());
    }

    public function testConstruct(): void
    {
        $this->entityManager->expects(static::once())->method('getClassMetadata');
        $this->registry->expects(static::once())->method('getManagerForClass');

        static::assertSame(TransactionEntity::class, $this->repository->getClassName());
    }

    public function testFindNewestBraintreeTransaction(): void
    {
        $shop = new ShopEntity('', '', '');

        $repository = $this->getMockBuilder(TransactionRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneBy'])
            ->getMock();

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(
                [
                    'shop' => $shop,
                    'orderTransactionId' => ['transaction-id-1', 'transaction-id-2'],
                ],
                ['createdAt' => 'DESC'],
            )
            ->willReturn(null);

        $repository->findNewestBraintreeTransaction($shop, ['transaction-id-1', 'transaction-id-2']);
    }

    public function testUpsertWithNewEntity(): void
    {
        $shop = new ShopEntity('', '', '');
        $data = [[
            'id' => null,
            'braintreeTransactionId' => 'transaction-id',
            'orderTransactionId' => 'order-id',
        ]];

        $this->entityManager
            ->expects(static::never())
            ->method('getReference');

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with(static::callback(static function (TransactionEntity $entity) use ($shop) {
                static::assertNull($entity->getId());
                static::assertSame('transaction-id', $entity->getBraintreeTransactionId());
                static::assertSame('order-id', $entity->getOrderTransactionId());
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

        $config = (new TransactionEntity())
            ->setId($id);

        $data = [
            'id' => (string) $id,
            'braintreeTransactionId' => 'transaction-id',
            'orderTransactionId' => 'order-id',
        ];

        $this->entityManager
            ->expects(static::once())
            ->method('getReference')
            ->with(TransactionEntity::class, $id)
            ->willReturn($config);

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with(static::callback(static function (TransactionEntity $entity) use ($shop, $config, $data) {
                static::assertSame($config, $entity);
                static::assertSame($data['id'], (string) $entity->getId());
                static::assertSame('transaction-id', $entity->getBraintreeTransactionId());
                static::assertSame('order-id', $entity->getOrderTransactionId());
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
