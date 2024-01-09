<?php

declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Controller;

use Braintree\ClientTokenGateway;
use Braintree\Gateway;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Util\SalesChannelConfigService;
use Swag\Braintree\Controller\StorefrontController;
use Swag\Braintree\Entity\ShopEntity;

#[CoversClass(StorefrontController::class)]
class StorefrontControllerTest extends TestCase
{
    private MockObject&Gateway $gateway;

    private MockObject&SalesChannelConfigService $salesChannelConfigService;

    private StorefrontController $controller;

    protected function setUp(): void
    {
        $this->gateway = $this->createMock(Gateway::class);
        $this->salesChannelConfigService = $this->createMock(SalesChannelConfigService::class);
        $this->controller = new StorefrontController(
            $this->gateway,
            $this->salesChannelConfigService,
        );
    }

    public function testGetClientTokenReturnsJsonResponseWithToken(): void
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
            ->with('this-is-sales-channel-id', 'this-is-currency-id', $shop)
            ->willReturn('this-is-merchant-id');

        $response = $this->controller->getClientToken($shop, 'this-is-currency-id', 'this-is-sales-channel-id');

        $json = \json_decode($response->getContent(), true);

        static::assertNotNull($json);
        static::assertSame(['token' => 'this-is-client-token'], $json);
    }
}
