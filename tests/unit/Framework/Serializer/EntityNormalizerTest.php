<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Framework\Serializer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\AppBundle\Entity\AbstractShop;
use Swag\Braintree\Entity\Contract\EntityInterface;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Framework\Serializer\EntityNormalizer;
use Swag\Braintree\Tests\Entity;
use Swag\Braintree\Tests\IdsCollection;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

#[CoversClass(EntityNormalizer::class)]
class EntityNormalizerTest extends TestCase
{
    private IdsCollection $ids;

    private EntityNormalizer $normalizer;

    protected function setUp(): void
    {
        $objectNormalizer = $this->createMock(ObjectNormalizer::class);
        $objectNormalizer
            ->expects(static::any())
            ->method('normalize')
            ->willReturnCallback(fn (mixed $object) => $object);

        $this->normalizer = new EntityNormalizer($objectNormalizer);
        $this->ids = new IdsCollection();
    }

    public function testSupportsNormalization(): void
    {
        static::assertTrue($this->normalizer->supportsNormalization($this->createMock(EntityInterface::class)));
        static::assertTrue($this->normalizer->supportsNormalization($this->createMock(AbstractShop::class)));
        static::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testNormalizeWithEntity(): void
    {
        $entity = new Entity();
        $entity->setId($this->ids->getUuid('entity'));

        static::assertSame($entity, $this->normalizer->normalize($entity, null, [EntityNormalizer::ORIGINAL_DATA => $entity]));
        static::assertSame($entity, $this->normalizer->normalize($entity));

        static::assertSame($this->ids->get('entity'), $this->normalizer->normalize($entity, null, [EntityNormalizer::ORIGINAL_DATA => new ShopEntity('', '', '')]));
    }

    public function testNormalizeWithShop(): void
    {
        $shop = new ShopEntity($this->ids->get('shop'), '', '');

        static::assertSame($shop, $this->normalizer->normalize($shop, null, [EntityNormalizer::ORIGINAL_DATA => $shop]));
        static::assertSame($shop, $this->normalizer->normalize($shop));

        static::assertSame($this->ids->get('shop'), $this->normalizer->normalize($shop, null, [EntityNormalizer::ORIGINAL_DATA => new Entity()]));
    }

    public function testNormalizeWithException(): void
    {
        $entity = new Entity();
        $entity->setId($this->ids->getUuid('entity'));

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The class must be in namespace "Swag".');

        static::assertSame($this->ids->get('entity'), $this->normalizer->normalize($entity, null, [EntityNormalizer::ORIGINAL_DATA => new \stdClass()]));
    }

    public function testNormalizeSwagNamespaceWithException(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The class must be in namespace "Swag".');
        $this->normalizer->normalizeSwagNamespace('Proxies\__CG__\Braintree\Entity\Braintree');
    }

    public function testNormalizeNamespace(): void
    {
        static::assertSame('Swag\Entity\Braintree', $this->normalizer->normalizeSwagNamespace('Proxies\__CG__\Swag\Entity\Braintree'));
    }
}
