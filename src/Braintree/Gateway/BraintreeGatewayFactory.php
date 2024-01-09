<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Gateway;

use Braintree\Configuration;
use Braintree\Gateway;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Framework\Request\ShopResolver;
use Symfony\Component\HttpFoundation\RequestStack;

class BraintreeGatewayFactory
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ShopResolver $shopResolver,
    ) {
    }

    public function createBraintreeGateway(): Gateway
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            throw new \RuntimeException('No request found');
        }

        /** @var ShopEntity $shop */
        $shop = $this->shopResolver->resolveShop($request);

        $configuration = new Configuration(
            [
                'environment' => $shop->isBraintreeSandbox() ? 'sandbox' : 'production',
                'merchantId' => $shop->getBraintreeMerchantId(),
                'publicKey' => $shop->getBraintreePublicKey(),
                'privateKey' => $shop->getBraintreePrivateKey(),
            ]
        );

        return new Gateway($configuration);
    }
}
