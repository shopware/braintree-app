<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Tests\Contract;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Tests\Contract\OrderHelperTrait;

#[CoversTrait(OrderHelperTrait::class)]
class OrderHelperTraitTest extends TestCase
{
    public function testTrait(): void
    {
        $class = new class {
            use OrderHelperTrait;
        };

        $method = new \ReflectionMethod($class, 'createOrder');
        $params = $method->getParameters();

        static::assertTrue($method->isProtected());
        static::assertTrue($method->isStatic());
        static::assertCount(0, $params);
    }
}
