<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Payment;

use Braintree\Exception\NotFound;
use Braintree\Gateway;
use Braintree\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Braintree\Exception\BraintreePaymentException;
use Swag\Braintree\Braintree\Exception\BraintreeTransactionNotFoundException;
use Swag\Braintree\Braintree\Util\SalesChannelConfigService;
use Swag\Braintree\Entity\TransactionEntity;
use Swag\Braintree\Entity\TransactionReportEntity;
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
        private readonly EntityManagerInterface $em,
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
            'amount' => $payment->orderTransaction->getAmount()->getTotalPrice(),
            'billing' => $billing['address'],
            'channel' => BraintreePaymentService::BRAINTREE_BN_CODE,
            'customer' => $this->orderInformationService->extractCustomer($payment),
            'deviceData' => $payment->requestData[self::BRAINTREE_DEVICE_DATA] ?? null,
            'discountAmount' => $this->orderInformationService->extractDiscountAmount($payment),
            'lineItems' => $this->orderInformationService->extractLineItems($payment),
            'merchantAccountId' => $merchantId,
            'options' => [
                'submitForSettlement' => $this->salesChannelConfigService->submitForSettlement($salesChannelId, $payment->shop),
            ],
            'paymentMethodNonce' => $nonce,
            'purchaseOrderNumber' => $payment->order->getOrderNumber(),
            'shipping' => $shipping['address'],
            'shippingAmount' => $payment->order->getShippingCosts()->getTotalPrice(),
            'shipsFromPostalCode' => $this->salesChannelConfigService->getShipsFromPostalCode($salesChannelId, $payment->shop),
            'shippingTaxAmount' => $this->orderInformationService->extractShippingTaxAmount($payment),
            'taxAmount' => $this->orderInformationService->extractTaxAmount($payment),
            'taxExempt' => $payment->order->getTaxStatus() === 'tax-free',
            'customFields' => $this->orderInformationService->extractCustomFields($payment),
        ]);

        if (!$response->success) {
            // @infection-ignore-all - As if that line isn't painful enough
            throw new BraintreePaymentException($response->errors->deepAll()[0]->message ?? 'Unknown error occured', shop: $payment->shop);
        }

        if (!isset($response->transaction)) {
            throw new BraintreePaymentException('No transaction provided', shop: $payment->shop);
        }

        $this->saveTransaction($payment, $response->transaction);

        return $response->transaction;
    }

    public function extractNonce(PaymentPayAction $payment): string
    {
        if (!$payment->requestData) {
            /** @infection-ignore-all can not be tested */
            throw new BraintreePaymentException('No nonce provided', shop: $payment->shop);
        }

        if (!\array_key_exists(self::BRAINTREE_NONCE, $payment->requestData)) {
            throw new BraintreePaymentException('No nonce provided', shop: $payment->shop);
        }

        $nonce = $payment->requestData[self::BRAINTREE_NONCE];

        if (!\is_string($nonce)) {
            throw new BraintreePaymentException('No nonce provided', shop: $payment->shop);
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

    private function saveTransaction(PaymentPayAction $payment, Transaction $braintreeTransaction): void
    {
        $transaction = (new TransactionEntity())
            ->setBraintreeTransactionId($braintreeTransaction->id)
            ->setOrderTransactionId($payment->orderTransaction->getId())
            ->setShop($payment->shop);

        $report = (new TransactionReportEntity())
            ->setCurrencyIso($braintreeTransaction->currencyIsoCode)
            ->setTotalPrice((string) $braintreeTransaction->amount)
            ->setTransaction($transaction);

        $this->em->persist($transaction);
        $this->em->persist($report);

        $this->em->flush();
    }

    /**
     * @param string[] $transactions
     */
    public function getTransactionDetails(ShopInterface $shop, array $transactions): Transaction
    {
        $transaction = $this->transactionRepository->findNewestBraintreeTransaction($shop, $transactions);

        if (!$transaction) {
            throw new BraintreeTransactionNotFoundException($transactions, $shop);
        }

        try {
            $braintreeTransaction = $this->gateway->transaction()->find($transaction->getBraintreeTransactionId());
        } catch (NotFound) {
            throw new BraintreeTransactionNotFoundException($transactions, $shop, $transaction->getBraintreeTransactionId());
        }

        return $braintreeTransaction;
    }
}
