<?php declare(strict_types=1);

namespace Swag\Braintree\Entity\Contract;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\MappedSuperclass]
trait SalesChannelAwareTrait
{
    #[Groups(groups: ['admin-write'])]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $salesChannelId;

    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(?string $salesChannelId): self
    {
        $this->salesChannelId = $salesChannelId;

        return $this;
    }
}
