<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Framework\Serializer;

use Braintree\Instance;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Framework\Serializer\BraintreeNormalizer;
use Swag\Braintree\Tests\Entity;
use Symfony\Component\Uid\Uuid;

#[CoversClass(BraintreeNormalizer::class)]
class BraintreeNormalizerTest extends TestCase
{
    private BraintreeNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new BraintreeNormalizer();
    }

    public function testNormalize(): void
    {
        $id = (string) Uuid::v7();

        $instance = new TestInstance(['id' => $id]);

        static::assertEquals(
            [['id' => $id]],
            $this->normalizer->normalize([$instance])
        );
    }

    public function testSupportsNormalization(): void
    {
        $instance = new TestInstance([]);
        $entity = new Entity();

        static::assertTrue($this->normalizer->supportsNormalization([$instance]));

        static::assertFalse($this->normalizer->supportsNormalization([]));
        static::assertFalse($this->normalizer->supportsNormalization([$entity]));
        static::assertFalse($this->normalizer->supportsNormalization([$instance, $entity]));
        static::assertFalse($this->normalizer->supportsNormalization(null));
    }
}

class TestInstance extends Instance
{
}
