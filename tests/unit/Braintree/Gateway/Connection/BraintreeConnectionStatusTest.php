<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Braintree\Gateway\Connection;

use Braintree\MerchantAccount;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Gateway\Connection\BraintreeConnectionStatus;

#[CoversClass(BraintreeConnectionStatus::class)]
class BraintreeConnectionStatusTest extends TestCase
{
    public function testConstruct(): void
    {
        $account = MerchantAccount::factory([]);

        $status = new BraintreeConnectionStatus(
            BraintreeConnectionStatus::STATUS_CONNECTED,
            $account
        );

        static::assertSame(BraintreeConnectionStatus::STATUS_CONNECTED, $status->connectionStatus);
        static::assertSame($account, $status->merchantAccount);
    }

    public function testConnected(): void
    {
        $account = MerchantAccount::factory([]);

        $status = BraintreeConnectionStatus::connected($account);

        static::assertSame(BraintreeConnectionStatus::STATUS_CONNECTED, $status->connectionStatus);
        static::assertSame($account, $status->merchantAccount);
    }

    public function testPending(): void
    {
        $status = BraintreeConnectionStatus::pending();

        static::assertSame(BraintreeConnectionStatus::STATUS_PENDING, $status->connectionStatus);
        static::assertNull($status->merchantAccount);
    }

    public function testSuspended(): void
    {
        $status = BraintreeConnectionStatus::suspended();

        static::assertSame(BraintreeConnectionStatus::STATUS_SUSPENDED, $status->connectionStatus);
        static::assertNull($status->merchantAccount);
    }

    public function testDisconnected(): void
    {
        $status = BraintreeConnectionStatus::disconnected();

        static::assertSame(BraintreeConnectionStatus::STATUS_DISCONNECTED, $status->connectionStatus);
        static::assertNull($status->merchantAccount);
    }
}
