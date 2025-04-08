<?php declare(strict_types=1);

namespace Swag\Braintree\Controller;

use Psr\Http\Message\ResponseInterface;
use Shopware\App\SDK\Context\Cart\Error;
use Shopware\App\SDK\Context\Gateway\Checkout\CheckoutGatewayAction;
use Shopware\App\SDK\Context\Gateway\Context\ContextGatewayAction;
use Shopware\App\SDK\Context\Response\Customer\AddressResponseStruct;
use Shopware\App\SDK\Context\Response\Customer\CustomerResponseStruct;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Gateway\Checkout\CheckoutGatewayCommand;
use Shopware\App\SDK\Gateway\Checkout\Command\AddCartErrorCommand;
use Shopware\App\SDK\Gateway\Checkout\Command\RemovePaymentMethodCommand;
use Shopware\App\SDK\Gateway\Context\Command\ChangeCurrencyCommand;
use Shopware\App\SDK\Gateway\Context\Command\ChangeLanguageCommand;
use Shopware\App\SDK\Gateway\Context\Command\ChangePaymentMethodCommand;
use Shopware\App\SDK\Gateway\Context\Command\ChangeShippingLocationCommand;
use Shopware\App\SDK\Gateway\Context\Command\ChangeShippingMethodCommand;
use Shopware\App\SDK\Gateway\Context\Command\RegisterCustomerCommand;
use Shopware\App\SDK\Response\GatewayResponse;
use Swag\Braintree\Braintree\Gateway\BraintreeConnectionService;
use Swag\Braintree\Braintree\Gateway\Connection\BraintreeConnectionStatus;
use Swag\Braintree\Braintree\Util\SalesChannelConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/api/gateway', name: 'swag.braintree.api.gateway.')]
class GatewayController extends AbstractController
{
    public const CREDIT_CARD_TECHNICAL_NAME = 'payment_SwagBraintreeApp_credit_card';

    public function __construct(
        private readonly BraintreeConnectionService $connectionService,
        private readonly SalesChannelConfigService $salesChannelConfigService,
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

        $status = $this->connectionService->testConnection();

        if ($status->connectionStatus !== BraintreeConnectionStatus::STATUS_CONNECTED) {
            $commands->add(new RemovePaymentMethodCommand(self::CREDIT_CARD_TECHNICAL_NAME));
        }

        if (!$this->salesChannelConfigService->getMerchantId(
            $action->context->getSalesChannel()->getId(),
            $action->context->getCurrencyId(),
            $action->shop
        )) {
            $commands->add(new RemovePaymentMethodCommand(self::CREDIT_CARD_TECHNICAL_NAME));

            if ($action->context->getPaymentMethod()->getTechnicalName() === self::CREDIT_CARD_TECHNICAL_NAME) {
                $commands->add(new AddCartErrorCommand('Checkout with Braintree is currently not available in your currency', true, Error::LEVEL_ERROR));
            }
        }

        return GatewayResponse::createCheckoutGatewayResponse($commands);
    }

    #[Route(path: '/context', name: 'context', methods: [Request::METHOD_POST])]
    public function context(ContextGatewayAction $action): ResponseInterface
    {
        dump($action);

        /** @var Collection<ContextGatewayAction> $commands */
        $commands = new Collection();
        $commands->add(new ChangeShippingLocationCommand('GB', 'GB-ENG'));
        $commands->add(new ChangeShippingLocationCommand('GB', 'GB-ENG'));
        $commands->add(new ChangePaymentMethodCommand('payment_debitpayment'));
        $commands->add(new ChangeShippingMethodCommand('shipping_express'));

        return GatewayResponse::createContextGatewayResponse($commands);
    }

    private function createMinimalCustomer(SalesChannelContext $context): CustomerResponseStruct
    {
        $customer = new CustomerResponseStruct();
        $customer->firstName = 'John';
        $customer->lastName = 'Doe';
        // random string
        $customer->email = bin2hex(random_bytes(5)) . '@example.com';
        $customer->billingAddress = $this->createMinimalAddress();
        $customer->storefrontUrl = $context->getSalesChannel()->getDomains()->first()->getUrl();
        $customer->acceptedDataProtection = true;
        $customer->password = 'michelistdoof';
        $customer->guest = false;

        return $customer;
    }

    private function createMinimalAddress(): AddressResponseStruct
    {
        $address = new AddressResponseStruct();
        $address->firstName = 'John';
        $address->lastName = 'Doe';
        $address->street = '123 Test Street';
        $address->zipcode = '12345';
        $address->city = 'Testville';
        $address->countryId = '0195d707eeb4708ca1823b8dce5c9ac2';

        return $address;
    }

}
