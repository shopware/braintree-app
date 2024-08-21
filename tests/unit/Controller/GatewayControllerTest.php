<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\Cart\Error;
use Shopware\App\SDK\Context\Gateway\Checkout\CheckoutGatewayAction;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Gateway\Checkout\Command\RemovePaymentMethodCommand;
use Shopware\App\SDK\Test\MockShop;
use Swag\Braintree\Braintree\Gateway\BraintreeConnectionService;
use Swag\Braintree\Braintree\Gateway\Connection\BraintreeConnectionStatus;
use Swag\Braintree\Braintree\Util\SalesChannelConfigService;
use Swag\Braintree\Controller\GatewayController;

#[CoversClass(GatewayController::class)]
class GatewayControllerTest extends TestCase
{
    public function testCheckoutGateway(): void
    {
        $shop = new MockShop('123', 'https://example.com', 'shop-secret');
        $context = new SalesChannelContext([
            'context' => [
                'currencyId' => 'currency-eur',
            ],
            'salesChannel' => [
                'id' => 'sales-channel-id',
            ],
        ]);

        $paymentMethods = new Collection([
            GatewayController::CREDIT_CARD_TECHNICAL_NAME => 'payment-method-id',
        ]);

        $action = new CheckoutGatewayAction(
            $shop,
            new ActionSource('https://example.com', '1.0.0'),
            new Cart([]),
            $context,
            $paymentMethods,
            new Collection()
        );

        $connection = BraintreeConnectionStatus::connected();

        $connectionService = $this->createMock(BraintreeConnectionService::class);
        $connectionService
            ->expects(static::once())
            ->method('testConnection')
            ->willReturn($connection);

        $salesChannelConfigService = $this->createMock(SalesChannelConfigService::class);
        $salesChannelConfigService
            ->expects(static::once())
            ->method('getMerchantId')
            ->with(
                'sales-channel-id',
                'currency-eur',
                $shop
            )
            ->willReturn('a-valid-merchant-id');

        $controller = new GatewayController($connectionService, $salesChannelConfigService);
        $response = $controller->checkout($action);

        static::assertSame(200, $response->getStatusCode());
        $body = \json_decode($response->getBody()->getContents());

        static::assertSame([], $body);
    }

    public function testCheckoutGatewayWithoutAvailableBraintreeMethod(): void
    {
        $shop = new MockShop('123', 'https://example.com', 'shop-secret');
        $context = new SalesChannelContext([
            'context' => [
                'currencyId' => 'currency-eur',
            ],
            'salesChannel' => [
                'id' => 'sales-channel-id',
            ],
        ]);

        $paymentMethods = new Collection([
            'some-other-payment-method' => 'payment-method-id',
        ]);

        $action = new CheckoutGatewayAction(
            $shop,
            new ActionSource('https://example.com', '1.0.0'),
            new Cart([]),
            $context,
            $paymentMethods,
            new Collection()
        );

        $connectionService = $this->createMock(BraintreeConnectionService::class);
        $connectionService
            ->expects(static::never())
            ->method('testConnection');

        $salesChannelConfigService = $this->createMock(SalesChannelConfigService::class);
        $salesChannelConfigService
            ->expects(static::never())
            ->method('getMerchantId');

        $controller = new GatewayController($connectionService, $salesChannelConfigService);
        $response = $controller->checkout($action);

        static::assertSame(200, $response->getStatusCode());
        $body = \json_decode($response->getBody()->getContents());

        static::assertSame([], $body);
    }

    public function testCheckoutGatewayConnectionNotValid(): void
    {
        $shop = new MockShop('123', 'https://example.com', 'shop-secret');
        $context = new SalesChannelContext([
            'context' => [
                'currencyId' => 'currency-eur',
            ],
            'salesChannel' => [
                'id' => 'sales-channel-id',
            ],
        ]);

        $paymentMethods = new Collection([
            GatewayController::CREDIT_CARD_TECHNICAL_NAME => 'payment-method-id',
        ]);

        $action = new CheckoutGatewayAction(
            $shop,
            new ActionSource('https://example.com', '1.0.0'),
            new Cart([]),
            $context,
            $paymentMethods,
            new Collection()
        );

        $connection = BraintreeConnectionStatus::disconnected();

        $connectionService = $this->createMock(BraintreeConnectionService::class);
        $connectionService
            ->expects(static::once())
            ->method('testConnection')
            ->willReturn($connection);

        $salesChannelConfigService = $this->createMock(SalesChannelConfigService::class);
        $salesChannelConfigService
            ->expects(static::once())
            ->method('getMerchantId')
            ->with(
                'sales-channel-id',
                'currency-eur',
                $shop
            )
            ->willReturn('a-valid-merchant-id');

        $controller = new GatewayController($connectionService, $salesChannelConfigService);
        $response = $controller->checkout($action);

        static::assertSame(200, $response->getStatusCode());
        $body = \json_decode($response->getBody()->getContents(), true);

        static::assertCount(1, $body);
        $command = array_pop($body);

        static::assertArrayHasKey('command', $command);
        static::assertArrayHasKey('payload', $command);

        static::assertSame(RemovePaymentMethodCommand::KEY, $command['command']);
        static::assertSame(['paymentMethodTechnicalName' => GatewayController::CREDIT_CARD_TECHNICAL_NAME], $command['payload']);
    }

    public function testCheckoutGatewayCurrencyNotConfigured(): void
    {
        $shop = new MockShop('123', 'https://example.com', 'shop-secret');
        $context = new SalesChannelContext([
            'context' => [
                'currencyId' => 'currency-eur',
            ],
            'paymentMethod' => [
                'technicalName' => GatewayController::CREDIT_CARD_TECHNICAL_NAME,
            ],
            'salesChannel' => [
                'id' => 'sales-channel-id',
            ],
        ]);

        $paymentMethods = new Collection([
            GatewayController::CREDIT_CARD_TECHNICAL_NAME => 'payment-method-id',
        ]);

        $action = new CheckoutGatewayAction(
            $shop,
            new ActionSource('https://example.com', '1.0.0'),
            new Cart([]),
            $context,
            $paymentMethods,
            new Collection()
        );

        $connection = BraintreeConnectionStatus::connected();

        $connectionService = $this->createMock(BraintreeConnectionService::class);
        $connectionService
            ->expects(static::once())
            ->method('testConnection')
            ->willReturn($connection);

        $salesChannelConfigService = $this->createMock(SalesChannelConfigService::class);
        $salesChannelConfigService
            ->expects(static::once())
            ->method('getMerchantId')
            ->with(
                'sales-channel-id',
                'currency-eur',
                $shop
            )
            ->willReturn(null);

        $controller = new GatewayController($connectionService, $salesChannelConfigService);
        $response = $controller->checkout($action);

        static::assertSame(200, $response->getStatusCode());
        $body = \json_decode($response->getBody()->getContents(), true);

        static::assertCount(2, $body);
        $command = \array_pop($body);

        static::assertArrayHasKey('command', $command);
        static::assertArrayHasKey('payload', $command);

        static::assertSame(
            [
                'message' => 'Checkout with Braintree is currently not available in your currency',
                'blocking' => true,
                'level' => Error::LEVEL_ERROR,
            ],
            $command['payload']
        );

        $command = \array_pop($body);

        static::assertArrayHasKey('command', $command);
        static::assertArrayHasKey('payload', $command);

        static::assertSame(RemovePaymentMethodCommand::KEY, $command['command']);
        static::assertSame(['paymentMethodTechnicalName' => GatewayController::CREDIT_CARD_TECHNICAL_NAME], $command['payload']);
    }
}
