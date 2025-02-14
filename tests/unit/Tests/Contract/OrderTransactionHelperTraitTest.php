<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Tests\Contract;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Tests\Contract\OrderTransactionHelperTrait;

#[CoversTrait(OrderTransactionHelperTrait::class)]
class OrderTransactionHelperTraitTest extends TestCase
{
    public function testTrait(): void
    {
        $class = new class {
            use OrderTransactionHelperTrait;
        };

        $method = new \ReflectionMethod($class, 'createOrderTransaction');
        $params = $method->getParameters();

        static::assertTrue($method->isProtected());
        static::assertTrue($method->isStatic());
        static::assertCount(0, $params);
    }
}
