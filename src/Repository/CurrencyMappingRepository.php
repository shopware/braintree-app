<?php declare(strict_types=1);

namespace Swag\Braintree\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Swag\Braintree\Entity\CurrencyMappingEntity;

/**
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
