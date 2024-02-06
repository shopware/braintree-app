<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Braintree\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Exception\BraintreePaymentException;
use Swag\Braintree\Entity\ShopEntity;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(BraintreePaymentException::class)]
class BraintreePaymentExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new ShopEntity('this-is-shop-id', '', '');
        $previous = new \RuntimeException();
        $exception = new BraintreePaymentException('this-is-message', e: $previous, shop: $shop);

        static::assertSame('SWAG_BRAINTREE__PAYMENT_EXCEPTION', $exception->getErrorCode());
        static::assertSame('Braintree payment process failed: this-is-message', $exception->getMessage());
        static::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getStatusCode());
        static::assertSame($previous, $exception->getPrevious());
        static::assertSame($shop->getShopId(), $exception->getParameters()['shopId']);
    }
}
