<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Framework\Request;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Shop\ShopRepositoryInterface;
use Shopware\App\SDK\Shop\ShopResolver as ShopwareShopResolver;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Framework\Request\ShopResolver;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

#[CoversClass(ShopResolver::class)]
class ShopResolverTest extends TestCase
{
    public function testResolveShop(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set(ShopResolver::SHOP_ID, 'shopId');

        $request = new Request();
        $request->setSession($session);

        $shop = new ShopEntity('shopId', 'shopUrl', 'shopSecret');

        $shopwareResolver = $this->createMock(ShopwareShopResolver::class);
        $shopwareResolver
            ->expects(static::never())
            ->method('resolveShop');

        $repo = $this->createMock(ShopRepositoryInterface::class);
        $repo
            ->expects(static::once())
            ->method('getShopFromId')
            ->with('shopId')
            ->willReturn($shop);

        $resolver = new ShopResolver(
            $shopwareResolver,
            $repo,
            $this->createMock(HttpMessageFactoryInterface::class)
        );

        $resolvedShop = $resolver->resolveShop($request);

        static::assertSame($shop, $resolvedShop);
    }

    public function testResolveShopWithNoSession(): void
    {
        $request = new Request();

        $shop = new ShopEntity('shopId', 'shopUrl', 'shopSecret');

        $shopwareResolver = $this->createMock(ShopwareShopResolver::class);
        $shopwareResolver
            ->expects(static::once())
            ->method('resolveShop')
            ->willReturn($shop);

        $repo = $this->createMock(ShopRepositoryInterface::class);
        $repo
            ->expects(static::never())
            ->method('getShopFromId');

        $factory = $this->createMock(HttpMessageFactoryInterface::class);
        $factory
            ->expects(static::once())
            ->method('createRequest')
            ->with($request)
            ->willReturn(new ServerRequest($request->getMethod(), 'http://example.com'));

        $resolver = new ShopResolver($shopwareResolver, $repo, $factory);

        $resolvedShop = $resolver->resolveShop($request);

        static::assertSame($shop, $resolvedShop);
    }

    public function testResolveShopWithSessionButNoShopId(): void
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $shop = new ShopEntity('shopId', 'shopUrl', 'shopSecret');

        $shopwareResolver = $this->createMock(ShopwareShopResolver::class);
        $shopwareResolver
            ->expects(static::once())
            ->method('resolveShop')
            ->willReturn($shop);

        $repo = $this->createMock(ShopRepositoryInterface::class);
        $repo
            ->expects(static::never())
            ->method('getShopFromId');

        $factory = $this->createMock(HttpMessageFactoryInterface::class);
        $factory
            ->expects(static::once())
            ->method('createRequest')
            ->with($request)
            ->willReturn(new ServerRequest($request->getMethod(), 'http://example.com'));

        $resolver = new ShopResolver($shopwareResolver, $repo, $factory);

        $resolvedShop = $resolver->resolveShop($request);

        static::assertSame($shop, $resolvedShop);
    }

    public function testResolveShopWithSessionShopNotFound(): void
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $request->getSession()->set('shop-id', 'shopId');

        $shop = new ShopEntity('shopId', 'shopUrl', 'shopSecret');

        $shopwareResolver = $this->createMock(ShopwareShopResolver::class);
        $shopwareResolver
            ->expects(static::once())
            ->method('resolveShop')
            ->willReturn($shop);

        $repo = $this->createMock(ShopRepositoryInterface::class);
        $repo
            ->expects(static::once())
            ->method('getShopFromId')
            ->with('shopId')
            ->willReturn(null);

        $factory = $this->createMock(HttpMessageFactoryInterface::class);
        $factory
            ->expects(static::once())
            ->method('createRequest')
            ->with($request)
            ->willReturn(new ServerRequest($request->getMethod(), 'http://example.com'));

        $resolver = new ShopResolver($shopwareResolver, $repo, $factory);

        $resolvedShop = $resolver->resolveShop($request);

        static::assertSame($shop, $resolvedShop);
    }
}
