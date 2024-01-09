<?php declare(strict_types=1);

namespace Swag\Braintree\Controller;

use Braintree\Gateway;
use Swag\Braintree\Braintree\Util\SalesChannelConfigService;
use Swag\Braintree\Entity\ShopEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(path: '/api')]
class StorefrontController extends AbstractController
{
    public function __construct(
        private readonly Gateway $gateway,
        private readonly SalesChannelConfigService $salesChannelConfigService,
    ) {
    }

    #[Route(
        path: '/client/token',
        name: 'braintree.client.token',
        methods: [Request::METHOD_POST]
    )]
    public function getClientToken(
        ShopEntity $shop,
        #[MapQueryParameter(name: 'currency-id')]
        string $currencyId,
        #[MapQueryParameter(name: 'sales-channel-id')]
        string $salesChannelId,
    ): Response {
        $merchantAccount = $this->salesChannelConfigService->getMerchantId($salesChannelId, $currencyId, $shop);

        /** @phpstan-ignore-next-line */
        $token = $this->gateway->clientToken()->generate([
            'merchantAccountId' => $merchantAccount,
        ]);

        return new JsonResponse(['token' => $token]);
    }
}
