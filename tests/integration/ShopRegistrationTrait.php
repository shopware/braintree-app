<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Integration;

use PHPUnit\Framework\Attributes\Before;
use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\App\SDK\Shop\ShopRepositoryInterface;
use Swag\Braintree\Entity\CurrencyMappingEntity;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Framework\Request\ShopResolver;
use Swag\Braintree\Repository\CurrencyMappingRepository;
use Swag\Braintree\Repository\ShopRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

trait ShopRegistrationTrait
{
    protected static function registerShop(string $shopId = '123456'): void
    {
        if (!static::$kernel) {
            static::bootKernel();
        }

        /** @var ContainerInterface $container */
        $container = static::getContainer();
        $container
            ->get(ShopRepository::class)
            ->upsert([[
                'shopId' => $shopId,
                'shopUrl' => 'https://shop.com',
                'shopSecret' => 'devsecret',
            ]], new ShopEntity($shopId, 'https://shop.com', 'devsecret'));

        $session = new Session(new MockFileSessionStorage());
        $session->set(ShopResolver::SHOP_ID, $shopId);

        $request = new Request();
        $request->setSession($session);

        $container
            ->get('request_stack')
            ->push($request);
    }

    protected static function allowCurrency(string $currency, string $shopId, ?string $salesChannelId = null): void
    {
        if (!static::$kernel) {
            static::bootKernel();
        }

        /** @var ContainerInterface $container */
        $container = static::getContainer();

        $shop = $container
            ->get(ShopRepository::class)
            ->find($shopId);

        $container
            ->get(CurrencyMappingRepository::class)
            ->upsert([[
                'currencyId' => $currency,
                'salesChannelId' => $salesChannelId,
                'merchantAccountId' => 'merchant-id',
                'currencyIso' => $currency,
            ]], $shop);
    }
}