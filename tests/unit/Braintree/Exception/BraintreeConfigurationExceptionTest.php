<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Braintree\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Exception\BraintreeConfigurationException;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(BraintreeConfigurationException::class)]
class BraintreeConfigurationExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $previous = new \RuntimeException();
        $exception = new BraintreeConfigurationException($previous);

        static::assertSame('SWAG_BRAINTREE__CONFIGURATION_EXCEPTION', $exception->getErrorCode());
        static::assertSame('Braintree configuration is invalid.', $exception->getMessage());
        static::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getStatusCode());
        static::assertSame($previous, $exception->getPrevious());
    }
}
