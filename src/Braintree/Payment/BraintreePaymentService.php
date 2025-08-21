<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Payment;

use Braintree\Exception\NotFound;
use Braintree\Gateway;
use Braintree\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Braintree\Exception\BraintreePaymentException;
use Swag\Braintree\Braintree\Exception\BraintreeTransactionNotFoundException;
use Swag\Braintree\Braintree\Util\SalesChannelConfigService;
use Swag\Braintree\Entity\TransactionEntity;
use Swag\Braintree\Entity\TransactionReportEntity;
use Swag\Braintree\Framework\Logger\LogProcessor;
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
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handleTransaction(PaymentPayAction $payment): Transaction
    {
        $this->logger->notice('Handle transaction', [LogProcessor::ACTION => $payment]);

        $currencyId = $this->orderInformationService->extractCurrencyId($payment);
        $salesChannelId = $this->orderInformationService->extractSalesChannelId($payment);
        $merchantId = $this->salesChannelConfigService->getMerchantId($salesChannelId, $currencyId, $payment->shop);

        if (!$merchantId) {
            throw new BraintreePaymentException('Braintree is not supported for the selected currency');
        }

        $nonce = $this->extractNonce($payment);
        $threeDSecureEnforced = $this->salesChannelConfigService->isThreeDSecureEnforced($salesChannelId, $payment->shop);

        $hasThreeDSecure = $this->validateThreeDSecure($nonce, $threeDSecureEnforced);

        $sale = [
            'amount' => $payment->orderTransaction->getAmount()->getTotalPrice(),
            'billing' => $this->orderInformationService->extractBillingAddress($payment)['address'],
            'channel' => BraintreePaymentService::BRAINTREE_BN_CODE,
            'customer' => $this->orderInformationService->extractCustomer($payment),
            'deviceData' => $payment->requestData[self::BRAINTREE_DEVICE_DATA] ?? null,
            'discountAmount' => $this->orderInformationService->extractDiscountAmount($payment),
            'lineItems' => $this->orderInformationService->extractLineItems($payment),
            'merchantAccountId' => $merchantId,
            'options' => [
                'submitForSettlement' => true,
                // Braintree will set this to true themselves if it is unset and 3DS has been completed
                ...($threeDSecureEnforced ? ['threeDSecure' => ['required' => true]] : []),
            ],
            'paymentMethodNonce' => $nonce,
            'purchaseOrderNumber' => $payment->order->getOrderNumber(),
            'shipping' => $this->orderInformationService->extractShippingAddress($payment)['address'],
            'shippingAmount' => $payment->order->getShippingCosts()->getTotalPrice(),
            'shipsFromPostalCode' => $this->salesChannelConfigService->getShipsFromPostalCode($salesChannelId, $payment->shop),
            'shippingTaxAmount' => $this->orderInformationService->extractShippingTaxAmount($payment),
            'taxAmount' => $this->orderInformationService->extractTaxAmount($payment),
            'taxExempt' => $payment->order->getTaxStatus() === 'tax-free',
            'customFields' => $this->orderInformationService->extractCustomFields($payment),
        ];

        $this->logger->notice('Sale transaction', [
            LogProcessor::ACTION => $payment,
            '3ds' => $hasThreeDSecure,
            '3dsEnforced' => $threeDSecureEnforced,
            'hasDeviceData' => !empty($sale['deviceData']),
            'hasCustomFields' => !empty($sale['customFields']),
            'merchantAccountId' => $sale['merchantAccountId'],
        ]);

        $response = $this->gateway->transaction()->sale($sale);

        if (!$response->success) {
            // @infection-ignore-all - As if that line isn't painful enough
            $errorMessage = $response->errors->deepAll()[0]->message ?? null;
            $responseMessage = $response->__isset('message') ? $response->message : null;

            // @infection-ignore-all - As if that line isn't painful enough
            throw new BraintreePaymentException(
                $errorMessage ?? $responseMessage ?? 'Unknown error occured',
                [
                    'responseMessage' => $responseMessage,
                    'responseErrors' => \array_map(static fn ($error) => $error->message, $response->errors->deepAll()),
                ],
                transactionId: $response->transaction->id ?? null,
            );
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

    private function validateThreeDSecure(string $nonce, bool $enforced): bool
    {
        try {
            $nonceInfo = $this->gateway->paymentMethodNonce()->find($nonce);
        } catch (\Exception $e) {
            throw new BraintreePaymentException('3D secure validation failed', [], $e);
        }

        if (!$nonceInfo->threeDSecureInfo) {
            if (!$enforced) {
                return false;
            }

            throw new BraintreePaymentException('3D secure validation failed: No information given');
        }

        if (!ThreeDSecure::isValid($nonceInfo->threeDSecureInfo, $enforced)) {
            throw new BraintreePaymentException('3D secure validation failed with status "{{ status }}"', ['status' => $nonceInfo->threeDSecureInfo->status]);
        }

        return true;
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
