<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Entity\TransactionReportEntity;
use Swag\Braintree\Repository\TransactionReportRepository;

#[CoversClass(TransactionReportRepository::class)]
class TransactionReportRepositoryTest extends TestCase
{
    private MockObject&EntityManagerInterface $entityManager;

    private MockObject&ManagerRegistry $registry;

    private TransactionReportRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager
            ->method('getClassMetadata')
            ->with(TransactionReportEntity::class)
            ->willReturn(new ClassMetadata(TransactionReportEntity::class));

        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry
            ->method('getManagerForClass')
            ->with(TransactionReportEntity::class)
            ->willReturn($this->entityManager);

        $this->repository = new TransactionReportRepository($this->registry);
    }

    public function testConstruct(): void
    {
        $this->entityManager->expects(static::once())->method('getClassMetadata');
        $this->registry->expects(static::once())->method('getManagerForClass');

        static::assertSame(TransactionReportEntity::class, $this->repository->getClassName());
    }
}
