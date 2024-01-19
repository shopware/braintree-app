<?php declare(strict_types=1);

namespace Swag\Braintree\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Swag\Braintree\Entity\TransactionReportEntity;

/**
 * @method TransactionReportEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransactionReportEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransactionReportEntity[]    findAll()
 *
 * @extends ServiceEntityRepository<TransactionReportEntity>
 */
class TransactionReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionReportEntity::class);
    }
}
