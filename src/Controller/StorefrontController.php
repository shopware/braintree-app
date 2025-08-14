<?php declare(strict_types=1);

namespace Swag\Braintree\Controller;

use Braintree\Gateway;
use Shopware\App\SDK\Context\Storefront\StorefrontAction;
use Swag\Braintree\Braintree\Util\SalesChannelConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/api', format: 'json')]
class StorefrontController extends AbstractController
{
    public function __construct(
        private readonly Gateway $gateway,
        private readonly SalesChannelConfigService $salesChannelConfigService,
    ) {
    }

    #[Route(
        path: '/client/config',
        name: 'braintree.client.config',
        methods: [Request::METHOD_POST],
    )]
    public function getClientConfig(
        StorefrontAction $storefrontAction,
        #[MapQueryParameter(name: 'currency-id')]
        ?string $currencyId = null,
        #[MapQueryParameter(name: 'sales-channel-id')]
        ?string $salesChannelId = null,
    ): Response {
        $currencyId ??= $storefrontAction->claims->getCurrencyId();
        $salesChannelId ??= $storefrontAction->claims->getSalesChannelId();

        $merchantAccountId = $this->salesChannelConfigService->getMerchantId($salesChannelId, $currencyId, $storefrontAction->shop);
        $merchantAccount = $this->gateway->merchantAccount()->find($merchantAccountId);

        $token = $this->gateway->clientToken()->generate(['merchantAccountId' => $merchantAccountId]);

        $threeDSecureEnforced = $this->salesChannelConfigService->isThreeDSecureEnforced($salesChannelId, $storefrontAction->shop);

        return new JsonResponse([
            'threeDS' => [
                'enforced' => $threeDSecureEnforced,
                'enabled' => $merchantAccount->threeDSecure['v2']['enabled'] ?? false,
            ],
            'token' => $token,
        ]);
    }

    #[\Deprecated(message: 'Use braintree.client.config instead.', since: '4.0.0')]
    #[Route(
        path: '/client/token',
        name: 'braintree.client.token',
        methods: [Request::METHOD_POST]
    )]
    public function getClientToken(
        StorefrontAction $storefrontAction,
        #[MapQueryParameter(name: 'currency-id')]
        ?string $currencyId = null,
        #[MapQueryParameter(name: 'sales-channel-id')]
        ?string $salesChannelId = null,
    ): Response {
        return $this->redirectToRoute('braintree.client.config', ['currency-id' => $currencyId, 'sales-channel-id' => $salesChannelId]);
    }
}
