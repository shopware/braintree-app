<?php declare(strict_types=1);

namespace Swag\Braintree\Controller;

use Braintree\Exception\NotFound;
use Braintree\Gateway;
use Psr\Http\Message\ResponseInterface;
use Shopware\App\SDK\Context\Cart\Error;
use Shopware\App\SDK\Context\Gateway\Checkout\CheckoutGatewayAction;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Gateway\Checkout\CheckoutGatewayCommand;
use Shopware\App\SDK\Gateway\Checkout\Command\AddCartErrorCommand;
use Shopware\App\SDK\Gateway\Checkout\Command\RemovePaymentMethodCommand;
use Shopware\App\SDK\Response\GatewayResponse;
use Swag\Braintree\Braintree\Gateway\BraintreeConnectionService;
use Swag\Braintree\Braintree\Util\SalesChannelConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/api/gateway', name: 'swag.braintree.api.gateway.', format: 'json')]
class GatewayController extends AbstractController
{
    public const CREDIT_CARD_TECHNICAL_NAME = 'payment_SwagBraintreeApp_credit_card';

    public function __construct(
        private readonly BraintreeConnectionService $connectionService,
        private readonly SalesChannelConfigService $salesChannelConfigService,
        private readonly Gateway $gateway,
    ) {
    }

    #[Route(path: '/checkout', name: 'checkout')]
    public function checkout(CheckoutGatewayAction $action): ResponseInterface
    {
        /** @var Collection<CheckoutGatewayCommand> $commands */
        $commands = new Collection();

        if (!$action->paymentMethods->has(self::CREDIT_CARD_TECHNICAL_NAME)) {
            return GatewayResponse::createCheckoutGatewayResponse($commands);
        }
        
        $merchantId = $this->salesChannelConfigService->getMerchantId(
            $action->context->getSalesChannel()->getId(),
            $action->context->getCurrencyId(),
            $action->shop
        );

        try {
            $this->gateway->merchantAccount()->find($merchantId);
        } catch(NotFound) {
            $commands->add(new RemovePaymentMethodCommand(self::CREDIT_CARD_TECHNICAL_NAME));

            if ($action->context->getPaymentMethod()->getTechnicalName() === self::CREDIT_CARD_TECHNICAL_NAME) {
                $commands->add(new AddCartErrorCommand('Checkout with Braintree is currently not available in your currency', true, Error::LEVEL_ERROR));
            }
        } catch (\Throwable) {
            $commands->add(new RemovePaymentMethodCommand(self::CREDIT_CARD_TECHNICAL_NAME));
        }

        return GatewayResponse::createCheckoutGatewayResponse($commands);
    }
}
