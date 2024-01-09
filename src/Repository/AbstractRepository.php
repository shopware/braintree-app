<?php declare(strict_types=1);

namespace Swag\Braintree\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Entity\Contract\EntityInterface;
use Swag\Braintree\Entity\Contract\SalesChannelAwareTrait;
use Swag\Braintree\Entity\Contract\ShopAwareTrait;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @template T of EntityInterface|ShopInterface
 *
 * @template-extends ServiceEntityRepository<T>
 */
abstract class AbstractRepository extends ServiceEntityRepository
{
    protected SerializerInterface $serializer;

    protected DenormalizerInterface $denormalizer;

    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
    ) {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @param array<array<string, mixed>> $data
     */
    public function upsert(array $data, ShopInterface $shop): void
    {
        foreach ($data as $item) {
            $id = $item['id'] ?? null;

            $entity = $id
                ? $this->getEntityManager()->getReference($this->getClassName(), Uuid::fromString($id))
                : new ($this->getClassName());

            if ($this->hasTrait(SalesChannelAwareTrait::class) && isset($item['salesChannelId']) && $item['salesChannelId'] === 'null') {
                $item['salesChannelId'] = null;
            }

            $entity = $this->denormalizeInto($entity, $item);

            if ($this->hasTrait(ShopAwareTrait::class)) {
                /** @phpstan-ignore-next-line */
                $entity->setShop($shop);
            }

            $this->getEntityManager()->persist($entity);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param T $into Doctrine entity to merge data into
     * @param string $data Data to merge as strigified JSON
     *
     * @return T
     */
    public function deserializeInto(mixed $into, string $data): mixed
    {
        return $this->serializer->deserialize(
            $data,
            $this->getClassName(),
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $into,
                AbstractNormalizer::GROUPS => ['admin-write'],
            ],
        );
    }

    /**
     * @param T $into Doctrine entity to merge data into
     * @param array<string, mixed> $data Data to merge as strigified JSON
     *
     * @return T
     */
    public function denormalizeInto(mixed $into, array $data): mixed
    {
        return $this->denormalizer->denormalize(
            $data,
            $this->getClassName(),
            'array',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $into,
                AbstractNormalizer::GROUPS => ['admin-write'],
            ],
        );
    }

    /**
     * @param array<string> $ids
     */
    public function delete(array $ids): void
    {
        foreach ($ids as $id) {
            $this->getEntityManager()->remove(
                $this->getEntityManager()->getReference(
                    $this->getClassName(),
                    Uuid::fromString($id)
                )
            );
        }
    }

    #[Required]
    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    #[Required]
    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->denormalizer = $denormalizer;
    }

    /**
     * @param class-string $trait
     */
    private function hasTrait(string $trait): bool
    {
        $traits = \class_uses($this->getClassName());

        if (!$traits) {
            return false;
        }

        return \in_array($trait, $traits, true);
    }
}
