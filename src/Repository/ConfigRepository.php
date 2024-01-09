<?php declare(strict_types=1);

namespace Swag\Braintree\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Entity\ConfigEntity;
use Swag\Braintree\Framework\Exception\EntityPropertyRequiredException;
use Symfony\Component\Uid\Uuid;

/**
 * @method ConfigEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfigEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfigEntity[]    findAll()
 *
 * @extends AbstractRepository<ConfigEntity>
 */
class ConfigRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, ConfigEntity::class);
    }

    public function upsert(array $data, ShopInterface $shop): void
    {
        foreach ($data as $config) {
            $id = $config['id'] ?? null;

            if ($config['salesChannelId'] === null) {
                if (!\array_key_exists('threeDSecureEnforced', $config) || $config['threeDSecureEnforced'] === null) {
                    throw new EntityPropertyRequiredException('threeDSecureEnforced');
                }
            }

            $entity = $id
                ? $this->getEntityManager()->getReference(ConfigEntity::class, Uuid::fromString($id))
                : new ConfigEntity();

            if (isset($config['salesChannelId']) && $config['salesChannelId'] === 'null') {
                $config['salesChannelId'] = null;
            }

            $entity = $this->denormalizeInto($entity, $config);

            $this->getEntityManager()->persist($entity->setShop($shop));
        }

        $this->getEntityManager()->flush();
    }
}
