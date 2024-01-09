<?php declare(strict_types=1);

namespace Swag\Braintree\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Entity\TransactionEntity;

/**
 * @method TransactionEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransactionEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransactionEntity[]    findAll()
 *
 * @extends AbstractRepository<TransactionEntity>
 */
class TransactionRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionEntity::class);
    }

    /**
     * @param string[] $transactions
     */
    public function findNewestBraintreeTransaction(ShopInterface $shop, array $transactions): ?TransactionEntity
    {
        return $this->findOneBy(
            [
                'shop' => $shop,
                'orderTransactionId' => $transactions,
            ],
            ['createdAt' => 'DESC'],
        );
    }
}
