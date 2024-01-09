<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Tests\Contract;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Tests\Contract\OrderHelperTrait;
use Swag\Braintree\Tests\IdsCollection;

#[CoversClass(OrderHelperTrait::class)]
class OrderHelperTraitTest extends TestCase
{
    public function testTrait(): void
    {
        $class = new class() {
            use OrderHelperTrait;
        };

        $method = new \ReflectionMethod($class, 'createOrder');
        $params = $method->getParameters();

        static::assertTrue($method->isPrivate());
        static::assertCount(1, $params);

        $paramType = $params[0]->getType();
        static::assertInstanceOf(\ReflectionNamedType::class, $paramType);
        static::assertEquals(IdsCollection::class, $paramType->getName());
    }
}
