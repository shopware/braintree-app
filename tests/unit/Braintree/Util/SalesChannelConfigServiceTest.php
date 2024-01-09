<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Braintree\Util;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Util\SalesChannelConfigService;
use Swag\Braintree\Entity\ConfigEntity;
use Swag\Braintree\Entity\CurrencyMappingEntity;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Repository\ConfigRepository;
use Swag\Braintree\Repository\CurrencyMappingRepository;

#[CoversClass(SalesChannelConfigService::class)]
class SalesChannelConfigServiceTest extends TestCase
{
    private ShopEntity $shop;

    private MockObject&ConfigRepository $configRepository;

    private MockObject&CurrencyMappingRepository $currencyMappingRepository;

    private SalesChannelConfigService $salesChannelConfigService;

    protected function setUp(): void
    {
        $this->shop = new ShopEntity('this-is-shop-id', '', '');
        $this->configRepository = $this->createMock(ConfigRepository::class);
        $this->currencyMappingRepository = $this->createMock(CurrencyMappingRepository::class);
        $this->salesChannelConfigService = new SalesChannelConfigService(
            $this->currencyMappingRepository,
            $this->configRepository,
        );
    }

    public function testGetMerchantIdBothReturnsSeparate(): void
    {
        $currencyMapping = (new CurrencyMappingEntity())
            ->setSalesChannelId('this-is-sales-channel-id')
            ->setMerchantAccountId('this-is-merchant-id');

        $defaultMapping = (new CurrencyMappingEntity())
            ->setSalesChannelId(null)
            ->setMerchantAccountId('this-is-merchant-id-2');

        $this->currencyMappingRepository
            ->expects(static::once())
            ->method('findBy')
            ->willReturn([$currencyMapping, $defaultMapping]);

        $merchantId = $this->salesChannelConfigService->getMerchantId(
            'this-is-sales-channel-id',
            'this-is-currency-id',
            $this->shop
        );

        static::assertEquals('this-is-merchant-id', $merchantId);
    }

    public function testGetMerchantIdBothButSeparateIsNull(): void
    {
        $currencyMapping = (new CurrencyMappingEntity())
            ->setSalesChannelId('this-is-sales-channel-id')
            ->setMerchantAccountId(null);

        $defaultMapping = (new CurrencyMappingEntity())
            ->setSalesChannelId(null)
            ->setMerchantAccountId('this-is-merchant-id');

        $this->currencyMappingRepository
            ->expects(static::once())
            ->method('findBy')
            ->willReturn([$currencyMapping, $defaultMapping]);

        $merchantId = $this->salesChannelConfigService->getMerchantId(
            'this-is-sales-channel-id',
            'this-is-currency-id',
            $this->shop
        );

        static::assertNull($merchantId);
    }

    public function testGetMerchantIdOnlySeparate(): void
    {
        $currencyMapping = (new CurrencyMappingEntity())
            ->setSalesChannelId('this-is-sales-channel-id')
            ->setMerchantAccountId('this-is-merchant-id');

        $this->currencyMappingRepository
            ->expects(static::once())
            ->method('findBy')
            ->willReturn([$currencyMapping]);

        $merchantId = $this->salesChannelConfigService->getMerchantId(
            'this-is-sales-channel-id',
            'this-is-currency-id',
            $this->shop
        );

        static::assertSame('this-is-merchant-id', $merchantId);
    }

    public function testGetMerchantIdOnlyDefault(): void
    {
        $defaultMapping = (new CurrencyMappingEntity())
            ->setSalesChannelId(null)
            ->setMerchantAccountId('this-is-merchant-id');

        $this->currencyMappingRepository
            ->expects(static::once())
            ->method('findBy')
            ->willReturn([$defaultMapping]);

        $merchantId = $this->salesChannelConfigService->getMerchantId(
            'this-is-sales-channel-id',
            'this-is-currency-id',
            $this->shop
        );

        static::assertSame('this-is-merchant-id', $merchantId);
    }

    public function testGetMerchantIdWithNone(): void
    {
        $shop = $this->shop;

        $this->currencyMappingRepository
            ->expects(static::once())
            ->method('findBy')
            ->with(static::callback(static function (array $criteria) use ($shop): bool {
                static::assertContains('this-is-sales-channel-id', $criteria['salesChannelId']);
                static::assertContains(null, $criteria['salesChannelId']);
                static::assertEquals('this-is-currency-id', $criteria['currencyId']);
                static::assertEquals($shop, $criteria['shop']);

                return true;
            }))
            ->willReturn([]);

        $merchantId = $this->salesChannelConfigService->getMerchantId(
            'this-is-sales-channel-id',
            'this-is-currency-id',
            $this->shop
        );

        static::assertNull($merchantId);
    }

    public function testIsThreeDSecureEnforcedBothButSeperateIsNull(): void
    {
        $config = (new ConfigEntity())
            ->setSalesChannelId('this-is-sales-channel-id')
            ->setThreeDSecureEnforced(null);

        $defaultConfig = (new ConfigEntity())
            ->setSalesChannelId(null)
            ->setThreeDSecureEnforced(true);

        $this->configRepository
            ->expects(static::once())
            ->method('findBy')
            ->willReturn([$config, $defaultConfig]);

        $enforcement = $this->salesChannelConfigService->isThreeDSecureEnforced(
            'this-is-sales-channel-id',
            $this->shop
        );

        static::assertTrue($enforcement);
    }

    public function testIsThreeDSecureEnforcedBothReturnsSeperate(): void
    {
        $config = (new ConfigEntity())
            ->setSalesChannelId('this-is-sales-channel-id')
            ->setThreeDSecureEnforced(true);

        $defaultConfig = (new ConfigEntity())
            ->setSalesChannelId(null)
            ->setThreeDSecureEnforced(false);

        $this->configRepository
            ->expects(static::once())
            ->method('findBy')
            ->willReturn([$config, $defaultConfig]);

        $enforcement = $this->salesChannelConfigService->isThreeDSecureEnforced(
            'this-is-sales-channel-id',
            $this->shop
        );

        static::assertTrue($enforcement);
    }

    public function testIsThreeDSecureEnforcedOnlySeperate(): void
    {
        $config = (new ConfigEntity())
            ->setSalesChannelId('this-is-sales-channel-id')
            ->setThreeDSecureEnforced(true);

        $this->configRepository
            ->expects(static::once())
            ->method('findBy')
            ->willReturn([$config]);

        $enforcement = $this->salesChannelConfigService->isThreeDSecureEnforced(
            'this-is-sales-channel-id',
            $this->shop
        );

        static::assertTrue($enforcement);
    }

    public function testIsThreeDSecureEnforcedOnlyDefault(): void
    {
        $defaultConfig = (new ConfigEntity())
            ->setSalesChannelId(null)
            ->setThreeDSecureEnforced(true);

        $this->configRepository
            ->expects(static::once())
            ->method('findBy')
            ->willReturn([$defaultConfig]);

        $enforcement = $this->salesChannelConfigService->isThreeDSecureEnforced(
            'this-is-sales-channel-id',
            $this->shop
        );

        static::assertTrue($enforcement);
    }

    public function testIsThreeDSecureEnforcedWithNone(): void
    {
        $shop = $this->shop;
        $this->configRepository
            ->expects(static::once())
            ->method('findBy')
            ->with(static::callback(static function (array $criteria) use ($shop): bool {
                static::assertContains('this-is-sales-channel-id', $criteria['salesChannelId']);
                static::assertContains(null, $criteria['salesChannelId']);
                static::assertEquals($shop, $criteria['shop']);

                return true;
            }))
            ->willReturn([]);

        $enforcement = $this->salesChannelConfigService->isThreeDSecureEnforced(
            'this-is-sales-channel-id',
            $this->shop
        );

        static::assertFalse($enforcement);
    }

    public function testIsThreeDSecureEnforcedWithBothNull(): void
    {
        $config = (new ConfigEntity())
            ->setSalesChannelId('this-is-sales-channel-id')
            ->setThreeDSecureEnforced(null);

        $defaultConfig = (new ConfigEntity())
            ->setSalesChannelId(null)
            ->setThreeDSecureEnforced(false);

        $this->configRepository
            ->expects(static::once())
            ->method('findBy')
            ->willReturn([$config, $defaultConfig]);

        $enforcement = $this->salesChannelConfigService->isThreeDSecureEnforced(
            'this-is-sales-channel-id',
            $this->shop
        );

        static::assertFalse($enforcement);
    }
}
