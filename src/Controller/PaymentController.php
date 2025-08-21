<?php declare(strict_types=1);

namespace Swag\Braintree\Controller;

use Braintree\Transaction;
use Psr\Log\LoggerInterface;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Response\PaymentResponse;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Braintree\Payment\BraintreePaymentService;
use Swag\Braintree\Framework\Exception\BraintreeHttpException;
use Swag\Braintree\Framework\Logger\LogProcessor;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(format: 'json')]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly BraintreePaymentService $paymentService,
        private readonly HttpFoundationFactoryInterface $httpFoundationFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(path: '/api/pay', name: 'swag.braintree.api.pay', methods: [Request::METHOD_POST])]
    public function pay(PaymentPayAction $payment): Response
    {
        try {
            $this->paymentService->handleTransaction($payment);
        } catch (BraintreeHttpException $e) {
            $this->logger->warning('Payment failed', [LogProcessor::EXCEPTION => $e, LogProcessor::ACTION => $payment]);

            return $this->httpFoundationFactory->createResponse(PaymentResponse::failed($e->getMessage()));
        }

        return $this->httpFoundationFactory->createResponse(PaymentResponse::paid());
    }

    #[Route(path: '/api/transaction/newest', name: 'swag.braintree.api.transaction', methods: [Request::METHOD_POST])]
    public function findBraintreeTransactionForOrderTransactions(Request $request, ShopInterface $shop): ?Transaction
    {
        $transactions = \json_decode($request->getContent(), true, flags: \JSON_THROW_ON_ERROR);

        if (!\is_array($transactions) || !\array_key_exists('transactions', $transactions)) {
            return null;
        }

        return $this->paymentService->getTransactionDetails($shop, $transactions['transactions']);
    }
}
