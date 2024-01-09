<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Tests\Serializer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Tests\Serializer\TestSerializer;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(TestSerializer::class)]
class TestSerializerTest extends TestCase
{
    public function testCreate(): void
    {
        $serializer = TestSerializer::create();

        static::assertInstanceOf(Serializer::class, $serializer);
    }
}
