<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Framework\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Framework\Exception\EntityPropertyRequiredException;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(EntityPropertyRequiredException::class)]
class EntityPropertyRequiredExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new EntityPropertyRequiredException('this-is-property');

        static::assertSame('SWAG_BRAINTREE__ENTITY_PROPERTY_EXCEPTION', $exception->getErrorCode());
        static::assertSame('"this-is-property" is required to be set', $exception->getMessage());
        static::assertSame(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());
        static::assertNull($exception->getPrevious());
    }
}
