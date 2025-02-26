<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Contract;

use Doctrine\ORM\EntityManagerInterface;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Tests\Ids;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @infection-ignore-all
 */
trait IntegrationHelperTrait
{
    abstract protected static function getContainer(): ContainerInterface;

    protected static function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine.orm.default_entity_manager');
    }

    protected static function createShop(
        string $shopIdKey = 'shop',
        string $shopUrl = 'https://shop.example.com',
        string $shopSecretKey = 'shop-secret',
        bool $persist = true,
    ): ShopEntity {
        $shop = new ShopEntity(
            Ids::get($shopIdKey),
            $shopUrl,
            Ids::get($shopSecretKey),
        );

        if ($persist) {
            self::getEntityManager()->persist($shop);
            self::getEntityManager()->flush();
        }

        return $shop;
    }
}
