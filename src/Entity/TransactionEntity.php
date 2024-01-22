<?php declare(strict_types=1);

namespace Swag\Braintree\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Swag\Braintree\Entity\Contract\EntityInterface;
use Swag\Braintree\Entity\Contract\EntityTrait;
use Swag\Braintree\Entity\Contract\ShopAwareTrait;
use Swag\Braintree\Repository\TransactionRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: '`transaction`')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'uniq_braintree_transaction_id_order_transaction_id', columns: ['braintree_transaction_id', 'order_transaction_id'])]
class TransactionEntity implements EntityInterface
{
    use EntityTrait;
    use ShopAwareTrait;

    #[Groups(groups: ['admin-write'])]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: false)]
    private string $braintreeTransactionId;

    #[Groups(groups: ['admin-write'])]
    #[ORM\Column(type: Types::STRING, nullable: false)]
    private string $orderTransactionId;

    #[ORM\OneToOne(targetEntity: TransactionReportEntity::class, mappedBy: 'transaction', cascade: ['persist', 'remove'])]
    private ?TransactionReportEntity $transactionReport = null;

    public function getBraintreeTransactionId(): string
    {
        return $this->braintreeTransactionId;
    }

    public function setBraintreeTransactionId(string $braintreeTransactionId): self
    {
        $this->braintreeTransactionId = $braintreeTransactionId;

        return $this;
    }

    public function getOrderTransactionId(): string
    {
        return $this->orderTransactionId;
    }

    public function setOrderTransactionId(string $orderTransactionId): self
    {
        $this->orderTransactionId = $orderTransactionId;

        return $this;
    }

    public function getTransactionReport(): ?TransactionReportEntity
    {
        return $this->transactionReport;
    }

    public function setTransactionReport(?TransactionReportEntity $transactionReport): self
    {
        $this->transactionReport = $transactionReport;

        return $this;
    }
}
