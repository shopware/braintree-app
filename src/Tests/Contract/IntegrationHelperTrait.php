<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Contract;

use Doctrine\ORM\EntityManagerInterface;
use Swag\Braintree\Entity\ShopEntity;

/**
 * @infection-ignore-all
 */
trait IntegrationHelperTrait
{
    protected static function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine.orm.default_entity_manager');
    }

    protected static function createShop(): ShopEntity
    {
        $shop = self::createShop();

        self::getEntityManager()->persist($shop);
        self::getEntityManager()->flush();

        return $shop;
    }
}
