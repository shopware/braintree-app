<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Application\Controller;

use Swag\Braintree\Entity\ConfigEntity;
use Swag\Braintree\Entity\CurrencyMappingEntity;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Tests\Contract\ApplicationHelperTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class BraintreeConfigurationControllerTest extends WebTestCase
{
    use ApplicationHelperTrait;

    public function testConfigDisconnect(): void
    {
        $shop = static::createShop('shop-to-disconnect', 'https://shop.example.com', 'shop-to-disconnect-secret', false);
        $shop->setBraintreeMerchantId('merchant-id');
        $shop->setBraintreePrivateKey('private-key');
        $shop->setBraintreePublicKey('public-key');
        $shop->setBraintreeSandbox(true);

        $config = new ConfigEntity();
        $config->setShop($shop);
        $config->setShipsFromPostalCode('12345');
        $config->setThreeDSecureEnforced(true);

        $currencyMapping = new CurrencyMappingEntity();
        $currencyMapping->setCurrencyId('euro-id');
        $currencyMapping->setCurrencyIso('EUR');
        $currencyMapping->setMerchantAccountId('merchant-account');
        $currencyMapping->setShop($shop);

        $entityManager = static::getEntityManager();
        $entityManager->persist($shop);
        $entityManager->persist($config);
        $entityManager->persist($currencyMapping);
        $entityManager->flush();
        // the controller has to load the shop from the database, like it does outside of tests
        $entityManager->clear();

        $client = static::createClientForShop($shop);
        $client->request(Request::METHOD_DELETE, '/api/config');

        // assert on what was written, not on the entity the controller changed in memory
        $entityManager->clear();
        $resetShop = $entityManager->find(ShopEntity::class, $shop->getShopId());

        static::assertNull($resetShop->getBraintreeMerchantId());
        static::assertNull($resetShop->getBraintreePrivateKey());
        static::assertNull($resetShop->getBraintreePublicKey());
        static::assertTrue($resetShop->isBraintreeSandbox());
        static::assertCount(1, $resetShop->getConfigs());
        static::assertCount(1, $resetShop->getCurrencyMappings());
    }
}
