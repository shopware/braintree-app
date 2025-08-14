<?php

declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Controller;

use Braintree\ClientTokenGateway;
use Braintree\Gateway;
use Braintree\MerchantAccountGateway;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Storefront\StorefrontAction;
use Shopware\App\SDK\Context\Storefront\StorefrontClaims;
use Shopware\App\SDK\Framework\Collection;
use Swag\Braintree\Braintree\Util\SalesChannelConfigService;
use Swag\Braintree\Controller\StorefrontController;
use Swag\Braintree\Entity\ShopEntity;

/**
 * @phpstan-import-type StorefrontClaimsArray from StorefrontClaims
 */
#[CoversClass(StorefrontController::class)]
class StorefrontControllerTest extends TestCase
{
    private MockObject&Gateway $gateway;

    private MockObject&SalesChannelConfigService $salesChannelConfigService;

    private MockObject&MerchantAccountGateway $merchantAccountGateway;

    private StorefrontController $controller;

    protected function setUp(): void
    {
        $this->merchantAccountGateway = $this->createMock(MerchantAccountGateway::class);

        $this->gateway = $this->createMock(Gateway::class);
        $this->gateway
            ->expects(static::any())
            ->method('merchantAccount')
            ->willReturn($this->merchantAccountGateway);

        $this->salesChannelConfigService = $this->createMock(SalesChannelConfigService::class);
        $this->controller = new StorefrontController(
            $this->gateway,
            $this->salesChannelConfigService,
        );
    }

    /**
     * @param StorefrontClaimsArray $queryParams
     * @param StorefrontClaimsArray $claims
     * @param StorefrontClaimsArray $expected
     */
    #[DataProvider('providerGetClientToken')]
    public function testGetClientToken(array $queryParams, array $claims, array $expected): void
    {
        $clientToken = $this->createMock(ClientTokenGateway::class);
        $clientToken
            ->expects(static::once())
            ->method('generate')
            ->with(['merchantAccountId' => 'this-is-merchant-id'])
            ->willReturn('this-is-client-token');

        $this->gateway
            ->expects(static::once())
            ->method('clientToken')
            ->willReturn($clientToken);

        $shop = new ShopEntity('', '', '');

        $this->salesChannelConfigService
            ->expects(static::once())
            ->method('getMerchantId')
            ->with($expected['salesChannelId'], $expected['currencyId'], $shop)
            ->willReturn('this-is-merchant-id');

        $this->salesChannelConfigService
            ->expects(static::once())
            ->method('isThreeDSecureEnforced')
            ->with($expected['salesChannelId'], $shop)
            ->willReturn(false);

        $this->merchantAccountGateway
            ->expects(static::once())
            ->method('find')
            ->with('this-is-merchant-id')
            ->willReturn((object) ['threeDSecure' => ['v2' => ['enabled' => false]]]);

        $action = new StorefrontAction($shop, new StorefrontClaims($claims), new Collection());

        $response = $this->controller->getClientConfig($action, ...$queryParams);

        $json = \json_decode($response->getContent(), true);

        static::assertNotNull($json);
        static::assertSame(['threeDS' => ['enforced' => false, 'enabled' => false], 'token' => 'this-is-client-token'], $json);
    }

    public static function providerGetClientToken(): \Generator
    {
        yield 'with query params' => [
            ['currencyId' => 'this-is-currency-id', 'salesChannelId' => 'this-is-sales-channel-id'],
            ['currencyId' => 'claim-currency-id', 'salesChannelId' => 'claim-sales-channel-id'],
            ['currencyId' => 'this-is-currency-id', 'salesChannelId' => 'this-is-sales-channel-id'],
        ];

        yield 'without query params' => [
            ['currencyId' => null, 'salesChannelId' => null],
            ['currencyId' => 'claim-currency-id', 'salesChannelId' => 'claim-sales-channel-id'],
            ['currencyId' => 'claim-currency-id', 'salesChannelId' => 'claim-sales-channel-id'],
        ];

        yield 'without sales channel id param' => [
            ['currencyId' => 'this-is-currency-id', 'salesChannelId' => null],
            ['currencyId' => 'claim-currency-id', 'salesChannelId' => 'claim-sales-channel-id'],
            ['currencyId' => 'this-is-currency-id', 'salesChannelId' => 'claim-sales-channel-id'],
        ];

        yield 'without currency id param' => [
            ['currencyId' => null, 'salesChannelId' => 'this-is-sales-channel-id'],
            ['currencyId' => 'claim-currency-id', 'salesChannelId' => 'claim-sales-channel-id'],
            ['currencyId' => 'claim-currency-id', 'salesChannelId' => 'this-is-sales-channel-id'],
        ];
    }
}
