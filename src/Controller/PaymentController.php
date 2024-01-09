<?php declare(strict_types=1);

namespace Swag\Braintree\Controller;

use Braintree\Transaction;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Response\PaymentResponse;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Braintree\Exception\BraintreePaymentException;
use Swag\Braintree\Braintree\Payment\BraintreePaymentService;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly BraintreePaymentService $paymentService,
        private readonly HttpFoundationFactoryInterface $httpFoundationFactory,
    ) {
    }

    #[Route(path: '/api/pay', name: 'swag.braintree.api.pay', methods: [Request::METHOD_POST])]
    public function pay(PaymentPayAction $payment): Response
    {
        try {
            $this->paymentService->handleTransaction($payment);
        } catch (BraintreePaymentException $e) {
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
