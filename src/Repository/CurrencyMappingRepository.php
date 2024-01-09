<?php declare(strict_types=1);

namespace Swag\Braintree\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Swag\Braintree\Entity\CurrencyMappingEntity;

/**
 * @method CurrencyMappingEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method CurrencyMappingEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method CurrencyMappingEntity[]    findAll()
 *
 * @extends AbstractRepository<CurrencyMappingEntity>
 */
class CurrencyMappingRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, CurrencyMappingEntity::class);
    }
}
