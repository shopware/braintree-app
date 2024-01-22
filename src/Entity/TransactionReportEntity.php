<?php declare(strict_types=1);

namespace Swag\Braintree\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Swag\Braintree\Repository\TransactionReportRepository;

#[ORM\Entity(repositoryClass: TransactionReportRepository::class)]
#[ORM\Table(name: 'transaction_report')]
#[ORM\HasLifecycleCallbacks]
class TransactionReportEntity
{
    #[ORM\Id, ORM\OneToOne(targetEntity: TransactionEntity::class)]
    private TransactionEntity $transaction;

    #[ORM\Column(type: Types::STRING, length: 3)]
    private string $currencyIso;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2)]
    private string $totalPrice;

    public function getTransaction(): TransactionEntity
    {
        return $this->transaction;
    }

    public function setTransaction(TransactionEntity $transaction): self
    {
        $this->transaction = $transaction;

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

    public function getTotalPrice(): string
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(string $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }
}
