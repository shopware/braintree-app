<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\Serializer;

use Braintree\Instance;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AutoconfigureTag(name: 'serializer.normalizer')]
class BraintreeNormalizer implements NormalizerInterface
{
    /**
     * @param Instance[]|Instance $object
     * @param array<string, mixed> $context
     *
     * @return array<int, array<string, mixed>>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if (\is_array($object)) {
            return \array_map(fn (Instance $braintreeObject) => $braintreeObject->toArray(), $object);
        }

        return $object->toArray();
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (\is_array($data) && \count($data) === 0) {
            return false;
        }

        if (\is_array($data)) {
            foreach ($data as $braintreeObject) {
                if (!\is_subclass_of($braintreeObject, Instance::class)) {
                    return false;
                }
            }

            return true;
        }

        return \is_subclass_of($data, Instance::class);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Instance::class => true,
        ];
    }
}
