<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Tests\Contract;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Tests\Contract\OrderHelperTrait;
use Swag\Braintree\Tests\Contract\OrderTransactionHelperTrait;
use Swag\Braintree\Tests\Contract\PaymentPayActionHelperTrait;
use Swag\Braintree\Tests\IdsCollection;

#[CoversClass(PaymentPayActionHelperTrait::class)]
class PaymentPayActionHelperTraitTest extends TestCase
{
    public function testTrait(): void
    {
        $class = new class() {
            use PaymentPayActionHelperTrait;
        };

        $method = new \ReflectionMethod($class, 'createPaymentPayAction');
        $params = $method->getParameters();

        static::assertTrue($method->isPrivate());
        static::assertCount(3, $params);

        $paramType1 = $params[0]->getType();
        $paramType2 = $params[1]->getType();
        static::assertInstanceOf(\ReflectionNamedType::class, $paramType1);
        static::assertInstanceOf(\ReflectionNamedType::class, $paramType2);
        static::assertEquals(IdsCollection::class, $paramType1->getName());
        static::assertEquals(ShopInterface::class, $paramType2->getName());

        $traits = \class_uses(PaymentPayActionHelperTrait::class);
        static::assertArrayHasKey(OrderHelperTrait::class, $traits);
        static::assertArrayHasKey(OrderTransactionHelperTrait::class, $traits);
    }
}
