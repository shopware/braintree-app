<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Framework\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Framework\Exception\ShopNotFoundException;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(ShopNotFoundException::class)]
class ShopNotFoundExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $previous = new \RuntimeException();
        $exception = new ShopNotFoundException('shop-id', $previous);

        static::assertSame('SWAG_BRAINTREE__SHOP_NOT_FOUND', $exception->getErrorCode());
        static::assertSame('Shop "shop-id" not found.', $exception->getMessage());
        static::assertSame(Response::HTTP_NOT_FOUND, $exception->getStatusCode());
        static::assertSame($previous, $exception->getPrevious());
    }
}
