<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Braintree\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Exception\BraintreeTransactionNotFoundException;
use Swag\Braintree\Entity\ShopEntity;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(BraintreeTransactionNotFoundException::class)]
class BraintreeTransactionNotFoundExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $orderTransactionIds = ['this-is-order-transaction-id'];
        $shop = new ShopEntity('this-is-shop-id', '', '');
        $exception = new BraintreeTransactionNotFoundException($orderTransactionIds, $shop, 'this-is-braintree-transaction-id');

        static::assertSame('SWAG_BRAINTREE__TRANSACTION_NOT_FOUND', $exception->getErrorCode());
        static::assertSame('Braintree transaction not found', $exception->getMessage());
        static::assertSame(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());

        $params = $exception->getParameters();
        static::assertSame($shop->getShopId(), $params['shopId']);
        static::assertSame($orderTransactionIds, $params['orderTransactionIds']);
        static::assertSame('this-is-braintree-transaction-id', $params['braintreeTransactionId']);
    }
}
