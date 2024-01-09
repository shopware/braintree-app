<?php declare(strict_types=1);

namespace Swag\Braintree\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Swag\Braintree\Entity\Contract\EntityInterface;
use Swag\Braintree\Entity\Contract\EntityTrait;
use Swag\Braintree\Entity\Contract\SalesChannelAwareTrait;
use Swag\Braintree\Entity\Contract\ShopAwareTrait;
use Swag\Braintree\Repository\CurrencyMappingRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CurrencyMappingRepository::class)]
#[ORM\Table(name: 'currency_mapping')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'uniq_config_id', columns: ['shop_id', 'sales_channel_id', 'currency_id', 'currency_iso'])]
#[ORM\UniqueConstraint(name: 'uniq_config_id', columns: ['shop_id', 'sales_channel_id', 'merchant_account_id', 'currency_iso'])]
class CurrencyMappingEntity implements EntityInterface
{
    use EntityTrait;
    use SalesChannelAwareTrait;
    use ShopAwareTrait;

    #[Groups(groups: ['admin-write'])]
    #[ORM\Column(type: Types::STRING)]
    private string $currencyId;

    #[Groups(groups: ['admin-write'])]
    #[ORM\Column(type: Types::STRING, length: 3)]
    private string $currencyIso;

    /**
     * Docs - "The merchant account ID cannot be longer than 32 characters."
     */
    #[Groups(groups: ['admin-write'])]
    #[ORM\Column(type: Types::STRING, length: 32, nullable: true)]
    private ?string $merchantAccountId = null;

    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    public function setCurrencyId(string $currencyId): self
    {
        $this->currencyId = $currencyId;

        return $this;
    }

    public function getCurrencyIso(): string
    {
        return $this->currencyIso;
    }

    public function setCurrencyIso(string $currencyIso): self
    {
        $this->currencyIso = $currencyIso;

        return $this;
    }

    public function getMerchantAccountId(): ?string
    {
        return $this->merchantAccountId;
    }

    public function setMerchantAccountId(?string $merchantAccountId): self
    {
        $this->merchantAccountId = $merchantAccountId;

        return $this;
    }
}
