<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Contract;

use Doctrine\ORM\EntityManagerInterface;
use Swag\Braintree\Entity\ConfigEntity;
use Swag\Braintree\Entity\CurrencyMappingEntity;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Tests\Ids;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @infection-ignore-all - this is static data
 */
trait ShopHelperTrait
{
    abstract protected static function getContainer(): ContainerInterface;

    protected static function createShop(): ShopEntity
    {
        return new ShopEntity(
            Ids::get('shop'),
            'https://shop.example.com',
            Ids::get('shop-secret'),
        );
    }

    protected static function registerShop(): ShopEntity
    {
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $shop = self::createShop();
        $shop->setBraintreeMerchantId($container->getParameter('BRAINTREE_TEST_MERCHANT_ID'));
        $shop->setBraintreePublicKey($container->getParameter('BRAINTREE_TEST_PUBLIC_KEY'));
        $shop->setBraintreePrivateKey($container->getParameter('BRAINTREE_TEST_PRIVATE_KEY'));
        $shop->setShopActive(true);
        $shop->setShopApiCredentials('this-is-the-client-id', 'this-is-the-client-secret');
        $shop->setBraintreeSandbox(true);

        $em->persist($shop);

        $config = new ConfigEntity();
        $config->setSalesChannelId(null);
        $config->setThreeDSecureEnforced(true);
        $config->setShipsFromPostalCode('48268');
        $config->setShop($shop);

        $em->persist($config);

        $currencyMapping = new CurrencyMappingEntity();
        $currencyMapping->setSalesChannelId(null);
        $currencyMapping->setCurrencyId(Ids::get('currency-id'));
        $currencyMapping->setCurrencyIso('EUR');
        $currencyMapping->setMerchantAccountId($container->getParameter('BRAINTREE_TEST_MERCHANT_ACCOUNT_ID'));
        $currencyMapping->setShop($shop);

        $em->persist($currencyMapping);
        $em->flush();

        return $shop;
    }
}
