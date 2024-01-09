<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Framework\ArgumentResolver;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Framework\ArgumentResolver\CriteriaArgumentResolver;
use Swag\Braintree\Framework\ArgumentResolver\Dto\Criteria;
use Swag\Braintree\Tests\Serializer\TestSerializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(CriteriaArgumentResolver::class)]
class CriteriaArgumentResolverTest extends TestCase
{
    private const UUID = 'ca3b21d0-e7f5-4622-950c-38d2c1bb5fd5';

    public function testResolve(): void
    {
        $request = new Request(content: '{"ids": ["' . self::UUID . '"]}');

        $serializer = TestSerializer::create();

        $resolver = new CriteriaArgumentResolver($serializer);
        $argument = new ArgumentMetadata('criteria', Criteria::class, false, false, null);

        $result = $resolver->resolve($request, $argument);

        static::assertIsIterable($result);
        $result = \iterator_to_array($result);

        static::assertCount(1, $result);

        $criteria = $result[0];
        static::assertNotNull($criteria);
        static::assertInstanceOf(Criteria::class, $criteria);
        static::assertCount(1, $criteria->ids);
        static::assertSame(self::UUID, (string) $criteria->ids[0]);
    }

    public function testResolveWithNoArgumentType(): void
    {
        $request = new Request(content: '{"ids": ["' . self::UUID . '"]}');

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer
            ->expects(static::never())
            ->method('deserialize');

        $resolver = new CriteriaArgumentResolver($serializer);
        $argument = new ArgumentMetadata('criteria', null, false, false, null);

        $result = $resolver->resolve($request, $argument);

        static::assertIsIterable($result);
        $result = \iterator_to_array($result);

        static::assertCount(0, $result);
    }

    public function testResolveWithNonCriteriaType(): void
    {
        $request = new Request(content: '{"ids": ["' . self::UUID . '"]}');

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer
            ->expects(static::never())
            ->method('deserialize');

        $resolver = new CriteriaArgumentResolver($serializer);
        $argument = new ArgumentMetadata('criteria', \stdClass::class, false, false, null);

        $result = $resolver->resolve($request, $argument);

        static::assertIsIterable($result);
        $result = \iterator_to_array($result);

        static::assertCount(0, $result);
    }
}
