<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Gateway;

use Braintree\Configuration;
use Braintree\Exception;
use Braintree\Exception\Authentication;
use Braintree\Gateway;
use Braintree\MerchantAccount;
use Swag\Braintree\Braintree\Gateway\Connection\BraintreeConnectionStatus;
use Swag\Braintree\Entity\ShopEntity;

class BraintreeConnectionService
{
    public function __construct(
        private readonly Gateway $gateway,
    ) {
    }

    public function fromShop(ShopEntity $shop): self
    {
        $gateway = new Gateway(
            new Configuration([
                'environment' => $shop->isBraintreeSandbox() ? 'sandbox' : 'production',
                'merchantId' => $shop->getBraintreeMerchantId(),
                'publicKey' => $shop->getBraintreePublicKey(),
                'privateKey' => $shop->getBraintreePrivateKey(),
            ])
        );

        return new self($gateway);
    }

    public function testConnection(): BraintreeConnectionStatus
    {
        $merchant = $this->getDefaultMerchantAccount();

        if ($merchant === null) {
            return BraintreeConnectionStatus::disconnected();
        }

        return BraintreeConnectionStatus::connected($merchant);
    }

    public function getDefaultMerchantAccount(): ?MerchantAccount
    {
        try {
            $accounts = $this->gateway->merchantAccount()->all();

            /** @var MerchantAccount $account */
            foreach ($accounts as $account) {
                if ($account->default) {
                    return $account;
                }
            }
        } catch (Exception) {
        }

        return null;
    }

    /**
     * @return MerchantAccount[]
     */
    public function getAllMerchantAccounts(): array
    {
        $accounts = [];

        try {
            /** @var MerchantAccount $account */
            foreach ($this->gateway->merchantAccount()->all() as $account) {
                $accounts[] = $account;
            }
        } catch (Authentication) {
        }

        return $accounts;
    }
}
