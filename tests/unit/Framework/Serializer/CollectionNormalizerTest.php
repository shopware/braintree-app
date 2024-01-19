<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Framework\Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\AppBundle\Entity\AbstractShop;
use Swag\Braintree\Entity\Contract\EntityInterface;
use Swag\Braintree\Framework\Serializer\CollectionNormalizer;
use Swag\Braintree\Tests\IdsCollection;

#[CoversClass(CollectionNormalizer::class)]
class CollectionNormalizerTest extends TestCase
{
    private IdsCollection $ids;

    private CollectionNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->ids = new IdsCollection();
        $this->normalizer = new CollectionNormalizer();
    }

    public function testNormalize(): void
    {
        $entity = $this->createMock(EntityInterface::class);
        $entity
            ->expects(static::once())
            ->method('getId')
            ->willReturn($this->ids->getUuid('entity'));

        $shop = $this->createMock(AbstractShop::class);
        $shop
            ->expects(static::once())
            ->method('getShopId')
            ->willReturn($this->ids->get('shop'));

        /** @var Collection<string, EntityInterface|AbstractShop> $collection */
        $collection = new ArrayCollection([
            $entity,
            $shop,
        ]);

        static::assertSame(
            [$this->ids->get('entity'), $this->ids->get('shop')],
            $this->normalizer->normalize($collection)
        );
    }

    public function testSupportsNormalization(): void
    {
        $entity = $this->createMock(EntityInterface::class);
        $shop = $this->createMock(AbstractShop::class);

        static::assertTrue($this->normalizer->supportsNormalization(new ArrayCollection()));
        static::assertTrue($this->normalizer->supportsNormalization(new ArrayCollection([$entity])));
        static::assertTrue($this->normalizer->supportsNormalization(new ArrayCollection([$shop])));
        static::assertTrue($this->normalizer->supportsNormalization(new ArrayCollection([$entity, $shop])));

        static::assertFalse($this->normalizer->supportsNormalization(new ArrayCollection([$entity, $shop, new \stdClass()])));
        static::assertFalse($this->normalizer->supportsNormalization([]));
        static::assertFalse($this->normalizer->supportsNormalization(''));
        static::assertFalse($this->normalizer->supportsNormalization(null));
    }

    public function testDenormalize(): void
    {
        $data = [['id' => 'this-is-id-1'], ['id' => 'this-is-id-2']];

        $expected = new ArrayCollection($data);

        static::assertEquals($expected, $this->normalizer->denormalize($data, ArrayCollection::class));
    }

    public function testSupportsDenormalization(): void
    {
        static::assertTrue($this->normalizer->supportsDenormalization([], ArrayCollection::class));

        static::assertFalse($this->normalizer->supportsDenormalization([], Collection::class));
        static::assertFalse($this->normalizer->supportsDenormalization(null, ArrayCollection::class));
        static::assertFalse($this->normalizer->supportsDenormalization('', Collection::class));
        static::assertFalse($this->normalizer->supportsDenormalization([], 'null'));
        static::assertFalse($this->normalizer->supportsDenormalization([], 'stdClass'));
    }

    public function testSupportedTypes(): void
    {
        static::assertSame(
            [
                AbstractShop::class => true,
                Collection::class => true,
            ],
            $this->normalizer->getSupportedTypes(null)
        );
    }
}
