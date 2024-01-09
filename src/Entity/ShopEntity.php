<?php declare(strict_types=1);

namespace Swag\Braintree\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Shopware\AppBundle\Entity\AbstractShop;
use Swag\Braintree\Entity\Contract\EntityDateTrait;
use Swag\Braintree\Repository\ShopRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Contracts\Service\ResetInterface;

#[ORM\Entity(repositoryClass: ShopRepository::class)]
#[ORM\Table(name: 'shop')]
#[ORM\HasLifecycleCallbacks]
class ShopEntity extends AbstractShop implements ResetInterface
{
    use EntityDateTrait;

    #[Groups(groups: ['admin-write'])]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $braintreePublicKey = null;

    #[Groups(groups: ['admin-write'])]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $braintreePrivateKey = null;

    #[Groups(groups: ['admin-write'])]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $braintreeMerchantId = null;

    #[Groups(groups: ['admin-write'])]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $braintreeSandbox = false;

    /**
     * @var Collection<string, ConfigEntity>
     */
    #[ORM\OneToMany(mappedBy: 'shop', targetEntity: ConfigEntity::class, cascade: ['remove'])]
    private Collection $configs;

    /**
     * @var Collection<string, CurrencyMappingEntity>
     */
    #[ORM\OneToMany(mappedBy: 'shop', targetEntity: CurrencyMappingEntity::class, cascade: ['remove'])]
    private Collection $currencyMappings;

    /**
     * @var Collection<string, TransactionEntity>
     */
    #[ORM\OneToMany(mappedBy: 'shop', targetEntity: TransactionEntity::class, cascade: ['persist', 'remove'])]
    private Collection $transactions;

    public function __construct(string $shopId, string $shopUrl, string $shopSecret)
    {
        $this->configs = new ArrayCollection();
        $this->currencyMappings = new ArrayCollection();

        parent::__construct($shopId, $shopUrl, $shopSecret);
    }

    public function reset(): void
    {
        $this->configs->clear();
        $this->currencyMappings->clear();
        $this->braintreeMerchantId = null;
        $this->braintreePrivateKey = null;
        $this->braintreePublicKey = null;
    }

    public function getBraintreePublicKey(): ?string
    {
        return $this->braintreePublicKey;
    }

    public function setBraintreePublicKey(?string $braintreePublicKey): self
    {
        $this->braintreePublicKey = $braintreePublicKey;

        return $this;
    }

    public function getBraintreePrivateKey(): ?string
    {
        return $this->braintreePrivateKey;
    }

    public function setBraintreePrivateKey(?string $braintreePrivateKey): self
    {
        $this->braintreePrivateKey = $braintreePrivateKey;

        return $this;
    }

    public function getBraintreeMerchantId(): ?string
    {
        return $this->braintreeMerchantId;
    }

    public function setBraintreeMerchantId(?string $braintreeMerchantId): self
    {
        $this->braintreeMerchantId = $braintreeMerchantId;

        return $this;
    }

    public function isBraintreeSandbox(): bool
    {
        return $this->braintreeSandbox;
    }

    public function setBraintreeSandbox(bool $braintreeSandbox): void
    {
        $this->braintreeSandbox = $braintreeSandbox;
    }

    /**
     * @return Collection<string, ConfigEntity>
     */
    public function getConfigs(): Collection
    {
        return $this->configs;
    }

    /**
     * @param Collection<string, ConfigEntity> $configs
     */
    public function setConfigs(Collection $configs): self
    {
        $this->configs = $configs;

        return $this;
    }

    /**
     * @return Collection<string, CurrencyMappingEntity>
     */
    public function getCurrencyMappings(): Collection
    {
        return $this->currencyMappings;
    }

    /**
     * @param Collection<string, CurrencyMappingEntity> $currencyMappings
     */
    public function setCurrencyMappings(Collection $currencyMappings): self
    {
        $this->currencyMappings = $currencyMappings;

        return $this;
    }

    /**
     * @return Collection<string, TransactionEntity>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    /**
     * @param Collection<string, TransactionEntity> $transactions
     */
    public function setTransactions(Collection $transactions): self
    {
        $this->transactions = $transactions;

        return $this;
    }
}
