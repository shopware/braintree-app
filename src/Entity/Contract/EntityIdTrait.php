<?php declare(strict_types=1);

namespace Swag\Braintree\Entity\Contract;

use Doctrine\ORM\Mapping as ORM;
use Swag\Braintree\Doctrine\RespectfulUuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\MappedSuperclass]
trait EntityIdTrait
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: RespectfulUuidGenerator::class)]
    // will never be null in the database
    // null only signalizes the uuid-generator to generate a new uuid for the database
    private ?Uuid $id = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(?Uuid $id): self
    {
        $this->id = $id;

        return $this;
    }
}
