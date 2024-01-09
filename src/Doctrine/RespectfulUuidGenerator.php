<?php declare(strict_types=1);

namespace Swag\Braintree\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Swag\Braintree\Entity\Contract\EntityInterface;
use Symfony\Component\Uid\Uuid;

class RespectfulUuidGenerator extends AbstractIdGenerator
{
    public function generate(EntityManager $em, $entity): ?Uuid
    {
        if (!$entity) {
            return null;
        }

        if (!$entity instanceof EntityInterface) {
            throw new \RuntimeException(
                \sprintf(
                    'Class %s not supported for respectful uuid generation. Use %s instead',
                    $entity::class,
                    EntityInterface::class
                )
            );
        }

        return $entity->getId() ?? Uuid::v7();
    }
}
