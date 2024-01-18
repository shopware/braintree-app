<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Payment;

use Braintree\Exception\NotFound;
use Braintree\Gateway;
use Braintree\Transaction;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Braintree\Exception\BraintreePaymentException;
use Swag\Braintree\Braintree\Util\SalesChannelConfigService;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Repository\TransactionRepository;

class BraintreePaymentService
{
    private const BRAINTREE_BN_CODE = 'shopwareAG_Cart_6_Braintree';

    public const BRAINTREE_NONCE = 'braintreeNonce';
    public const BRAINTREE_DEVICE_DATA = 'braintreeDeviceData';

    public function __construct(
        private readonly Gateway $gateway,
        private readonly OrderInformationService $orderInformationService,
        private readonly SalesChannelConfigService $salesChannelConfigService,
        private readonly TransactionRepository $transactionRepository,
    ) {
    }

    public function handleTransaction(PaymentPayAction $payment): Transaction
    {
        $currencyId = $this->orderInformationService->extractCurrencyId($payment);
        $salesChannelId = $this->orderInformationService->extractSalesChannelId($payment);
        $merchantId = $this->salesChannelConfigService->getMerchantId($salesChannelId, $currencyId, $payment->shop);

        if (!$merchantId) {
            throw new BraintreePaymentException('Braintree is not supported for the selected currency');
        }

        $nonce = $this->extractNonce($payment);
        $this->validateThreeDSecure($nonce, $this->salesChannelConfigService->isThreeDSecureEnforced($salesChannelId, $payment->shop));

        $billing = $this->orderInformationService->extractBillingAddress($payment);
        $shipping = $this->orderInformationService->extractShippingAddress($payment);

        $response = $this->gateway->transaction()->sale([
            'merchantAccountId' => $merchantId,
            'amount' => $payment->orderTransaction->getAmount()->getTotalPrice(),
            'billing' => $billing['address'],
            'customer' => $this->orderInformationService->extractCustomer($payment),
            'shippingAmount' => $payment->order->getShippingCosts()->getTotalPrice(),
            'deviceData' => $payment->requestData[self::BRAINTREE_DEVICE_DATA] ?? null,
            'discountAmount' => $this->orderInformationService->extractDiscountAmount($payment),
            'lineItems' => $this->orderInformationService->extractLineItems($payment),
            'shipping' => $shipping['address'],
            'options' => [
                'submitForSettlement' => true,
            ],
            'paymentMethodNonce' => $nonce,
            'purchaseOrderNumber' => $payment->order->getOrderNumber(),
            'taxAmount' => $this->orderInformationService->extractTaxAmount($payment),
            'channel' => BraintreePaymentService::BRAINTREE_BN_CODE,
        ]);

        if (!$response->success) {
            // @infection-ignore-all - As if that line isn't painful enough
            // @phpstan-ignore-next-line - Braintree SDK needs better typing
            throw new BraintreePaymentException($response->errors->deepAll()[0]?->message ?? 'Unknown error occured');
        }

        if (!isset($response->transaction)) {
            throw new BraintreePaymentException('No transaction provided');
        }

        $this->saveTransaction($payment, $response->transaction);

        return $response->transaction;
    }

    public function extractNonce(PaymentPayAction $payment): string
    {
        if (!$payment->requestData) {
            /** @infection-ignore-all can not be tested */
            throw new BraintreePaymentException('No nonce provided');
        }

        if (!\array_key_exists(self::BRAINTREE_NONCE, $payment->requestData)) {
            throw new BraintreePaymentException('No nonce provided');
        }

        $nonce = $payment->requestData[self::BRAINTREE_NONCE];

        if (!\is_string($nonce)) {
            throw new BraintreePaymentException('No nonce provided');
        }

        return $nonce;
    }

    private function validateThreeDSecure(string $nonce, bool $enforced): void
    {
        try {
            $nonceInfo = $this->gateway->paymentMethodNonce()->find($nonce);
        } catch (\Exception $e) {
            throw new BraintreePaymentException('3D secure validation failed', [], $e);
        }

        if (!$nonceInfo->threeDSecureInfo) {
            throw new BraintreePaymentException('3D secure validation failed');
        }

        if (!ThreeDSecure::isValid($nonceInfo->threeDSecureInfo, $enforced)) {
            throw new BraintreePaymentException('3D secure validation failed');
        }
    }

    /**
     * @param string[] $transactions
     */
    public function getTransactionDetails(ShopInterface $shop, array $transactions): Transaction
    {
        $transaction = $this->transactionRepository->findNewestBraintreeTransaction($shop, $transactions);

        if (!$transaction) {
            throw new BraintreePaymentException('No braintree transaction found');
        }

        try {
            $braintreeTransaction = $this->gateway->transaction()->find($transaction->getBraintreeTransactionId());
        } catch (NotFound) {
            throw new BraintreePaymentException('No braintree transaction found');
        }

        return $braintreeTransaction;
    }

    private function saveTransaction(PaymentPayAction $payment, Transaction $braintreeResponse): void
    {
        /** @var ShopEntity $shop */
        $shop = $payment->shop;

        $this->transactionRepository->upsert([
            [
                'orderTransactionId' => $payment->orderTransaction->getId(),
                'braintreeTransactionId' => $braintreeResponse->id,
            ],
        ], $shop);
    }
}
