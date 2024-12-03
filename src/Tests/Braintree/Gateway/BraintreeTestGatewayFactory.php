<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Braintree\Gateway;

use Braintree\Configuration;
use Braintree\Gateway;

/**
 * Replaces the Braintree gateway with test information to use during application/e2e tests
 *
 * @internal
 *
 * @infection-ignore-all
 */
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
