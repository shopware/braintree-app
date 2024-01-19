<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Shopware\AppBundle\Entity\AbstractShop;
use Swag\Braintree\Entity\Contract\EntityInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AutoconfigureTag(name: 'serializer.normalizer')]
class CollectionNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param Collection<string, EntityInterface|AbstractShop> $object
     * @param array<string, mixed> $context
     *
     * @return mixed[]
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        return $object->map(function (EntityInterface|AbstractShop $item) {
            if ($item instanceof AbstractShop) {
                return $item->getShopId();
            }

            return (string) $item->getId();
        })->toArray();
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof Collection && $data->forAll(function ($_, $item) {
            return $item instanceof EntityInterface || $item instanceof AbstractShop;
        });
    }

    /**
     * @param mixed[] $data
     *
     * @return Collection<string, mixed>
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): Collection
    {
        return new ArrayCollection($data);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return \is_subclass_of($type, Collection::class, true) && \is_array($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AbstractShop::class => true,
            Collection::class => true,
        ];
    }
}
