<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Tests\Contract;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Tests\Contract\OrderHelperTrait;
use Swag\Braintree\Tests\Contract\OrderTransactionHelperTrait;
use Swag\Braintree\Tests\Contract\PaymentPayActionHelperTrait;

#[CoversTrait(PaymentPayActionHelperTrait::class)]
class PaymentPayActionHelperTraitTest extends TestCase
{
    public function testTrait(): void
    {
        $class = new class {
            use PaymentPayActionHelperTrait;
        };

        $method = new \ReflectionMethod($class, 'createPaymentPayAction');
        $params = $method->getParameters();

        static::assertTrue($method->isProtected());
        static::assertTrue($method->isStatic());
        static::assertCount(2, $params);

        $paramType1 = $params[0]->getType();
        static::assertInstanceOf(\ReflectionNamedType::class, $paramType1);
        static::assertEquals(ShopInterface::class, $paramType1->getName());

        $traits = \class_uses(PaymentPayActionHelperTrait::class);
        static::assertArrayHasKey(OrderHelperTrait::class, $traits);
        static::assertArrayHasKey(OrderTransactionHelperTrait::class, $traits);
    }
}
