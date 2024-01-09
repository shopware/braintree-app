<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\unit\Braintree\Gateway;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Gateway\BraintreeGatewayFactory;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Framework\Request\ShopResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(BraintreeGatewayFactory::class)]
class BraintreeGatewayFactoryTest extends TestCase
{
    public function testCreateBraintreeGateway(): void
    {
        $shop = new ShopEntity('shopId', 'shopUrl', 'shopSecret');
        $shop->setBraintreeMerchantId('merchantId');
        $shop->setBraintreePublicKey('publicKey');
        $shop->setBraintreePrivateKey('privateKey');

        $request = new Request();

        $stack = $this->createMock(RequestStack::class);
        $stack
            ->expects(static::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $shopResolver = $this->createMock(ShopResolver::class);
        $shopResolver
            ->expects(static::once())
            ->method('resolveShop')
            ->with($request)
            ->willReturn($shop);

        $factory = new BraintreeGatewayFactory($stack, $shopResolver);
        $gateway = $factory->createBraintreeGateway();

        static::assertSame('production', $gateway->config->getEnvironment());
        static::assertSame('merchantId', $gateway->config->getMerchantId());
        static::assertSame('privateKey', $gateway->config->getPrivateKey());
        static::assertSame('publicKey', $gateway->config->getPublicKey());
    }

    public function testNoRequestThrows(): void
    {
        $stack = $this->createMock(RequestStack::class);
        $stack
            ->expects(static::once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $shopResolver = $this->createMock(ShopResolver::class);
        $shopResolver
            ->expects(static::never())
            ->method('resolveShop');

        $factory = new BraintreeGatewayFactory($stack, $shopResolver);

        $this->expectException(\RuntimeException::class);

        $factory->createBraintreeGateway();
    }
}
