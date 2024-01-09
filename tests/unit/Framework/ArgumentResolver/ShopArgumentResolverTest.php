<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Framework\ArgumentResolver;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Framework\ArgumentResolver\ShopArgumentResolver;
use Swag\Braintree\Framework\Request\ShopResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[CoversClass(ShopArgumentResolver::class)]
class ShopArgumentResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $request = new Request(query: ['shopId' => 'shop-id']);

        $shop = new ShopEntity('shop-id', 'shop-url', 'shop-secret');

        $shopResolver = $this->createMock(ShopResolver::class);
        $shopResolver
            ->expects(static::once())
            ->method('resolveShop')
            ->with($request)
            ->willReturn($shop);

        $argument = new ArgumentMetadata('shop', ShopInterface::class, false, false, null);

        $resolver = new ShopArgumentResolver($shopResolver);

        $result = $resolver->resolve($request, $argument);

        static::assertIsIterable($result);

        $result = \iterator_to_array($result);

        static::assertCount(1, $result);

        $shop = $result[0];

        static::assertInstanceOf(ShopInterface::class, $shop);
        static::assertSame('shop-id', $shop->getShopId());
    }

    public function testResolveWithNoArgumentType(): void
    {
        $request = new Request(query: ['shopId' => 'shop-id']);

        $shopResolver = $this->createMock(ShopResolver::class);
        $shopResolver
            ->expects(static::never())
            ->method('resolveShop');

        $argument = new ArgumentMetadata('shop', null, false, false, null);

        $resolver = new ShopArgumentResolver($shopResolver);

        $result = $resolver->resolve($request, $argument);

        static::assertIsIterable($result);

        $result = \iterator_to_array($result);

        static::assertCount(0, $result);
    }

    public function testResolveWithNonShopType(): void
    {
        $request = new Request(query: ['shopId' => 'shop-id']);

        $shop = new ShopEntity('shop-id', 'shop-url', 'shop-secret');

        $shopResolver = $this->createMock(ShopResolver::class);
        $shopResolver
            ->expects(static::never())
            ->method('resolveShop');

        $argument = new ArgumentMetadata('shop', \stdClass::class, false, false, null);

        $resolver = new ShopArgumentResolver($shopResolver);

        $result = $resolver->resolve($request, $argument);

        static::assertIsIterable($result);

        $result = \iterator_to_array($result);

        static::assertCount(0, $result);
    }
}
