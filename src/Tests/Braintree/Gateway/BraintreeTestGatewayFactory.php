<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Braintree\Gateway;

use Braintree\Configuration;
use Braintree\Gateway;

class BraintreeTestGatewayFactory
{
    public function __construct(
        private readonly string $braintreeEnv,
        private readonly string $braintreeMerchantId,
        private readonly string $braintreePublicKey,
        private readonly string $braintreePrivateKey,
    ) {
    }

    public function createBraintreeGateway(): Gateway
    {
        $configuration = new Configuration(
            [
                'environment' => $this->braintreeEnv,
                'merchantId' => $this->braintreeMerchantId,
                'publicKey' => $this->braintreePublicKey,
                'privateKey' => $this->braintreePrivateKey,
            ]
        );

        return new Gateway($configuration);
    }
}
