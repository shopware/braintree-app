<?php declare(strict_types=1);

namespace Swag\Braintree\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Entity\ShopEntity;

/**
 * @method ShopInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopInterface[]    findAll()
 *
 * @extends AbstractRepository<ShopInterface>
 */
class ShopRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, ShopEntity::class);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function upsert(array $data, ShopInterface $shop): void
    {
        $entity = $this->denormalizeInto($shop, $data);

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }
}
