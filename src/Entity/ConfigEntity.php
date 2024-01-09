<?php declare(strict_types=1);

namespace Swag\Braintree\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Swag\Braintree\Entity\Contract\EntityInterface;
use Swag\Braintree\Entity\Contract\EntityTrait;
use Swag\Braintree\Entity\Contract\SalesChannelAwareTrait;
use Swag\Braintree\Entity\Contract\ShopAwareTrait;
use Swag\Braintree\Repository\ConfigRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ConfigRepository::class)]
#[ORM\Table(name: 'config')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'uniq_sales_channel_id_shop_id', columns: ['sales_channel_id', 'shop_id'])]
class ConfigEntity implements EntityInterface
{
    use EntityTrait;
    use SalesChannelAwareTrait;
    use ShopAwareTrait;

    #[Groups(groups: ['admin-write'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $threeDSecureEnforced = null;

    public function isThreeDSecureEnforced(): ?bool
    {
        return $this->threeDSecureEnforced;
    }

    public function setThreeDSecureEnforced(?bool $threeDSecureEnforced): self
    {
        $this->threeDSecureEnforced = $threeDSecureEnforced;

        return $this;
    }
}
