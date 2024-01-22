<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Braintree\Payment;

use Braintree\Exception\NotFound;
use Braintree\Gateway;
use Braintree\PaymentMethodNonce;
use Braintree\PaymentMethodNonceGateway;
use Braintree\Result;
use Braintree\Transaction;
use Braintree\TransactionGateway;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Exception\BraintreePaymentException;
use Swag\Braintree\Braintree\Payment\BraintreePaymentService;
use Swag\Braintree\Braintree\Payment\OrderInformationService;
use Swag\Braintree\Braintree\Payment\Tax\TaxService;
use Swag\Braintree\Braintree\Payment\ThreeDSecure;
use Swag\Braintree\Braintree\Util\SalesChannelConfigService;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Entity\TransactionEntity;
use Swag\Braintree\Entity\TransactionReportEntity;
use Swag\Braintree\Repository\TransactionRepository;
use Swag\Braintree\Tests\Contract\PaymentPayActionHelperTrait;
use Swag\Braintree\Tests\IdsCollection;

#[CoversClass(BraintreePaymentService::class)]
class BraintreePaymentServiceTest extends TestCase
{
    use PaymentPayActionHelperTrait;

    private BraintreePaymentService $paymentService;

    private OrderInformationService $orderInformationService;

    private IdsCollection $orderIds;

    private ShopEntity $shop;

    private MockObject&Gateway $gateway;

    private MockObject&TransactionGateway $transactionGateway;

    private MockObject&PaymentMethodNonceGateway $paymentMethodNonceGateway;

    private MockObject&TransactionRepository $transactionRepository;

    private MockObject&EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->paymentMethodNonceGateway = $this->createMock(PaymentMethodNonceGateway::class);
        $this->transactionGateway = $this->createMock(TransactionGateway::class);

        $this->gateway = $this->createMock(Gateway::class);
        $this->gateway->method('paymentMethodNonce')->willReturn($this->paymentMethodNonceGateway);
        $this->gateway->method('transaction')->willReturn($this->transactionGateway);

        $salesChannelConfigService = $this->createMock(SalesChannelConfigService::class);
        $salesChannelConfigService->method('getMerchantId')->willReturn('this-is-merchant-id');
        $salesChannelConfigService->method('isThreeDSecureEnforced')->willReturn(true);

        $this->transactionRepository = $this->createMock(TransactionRepository::class);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->orderIds = new IdsCollection();
        $this->orderInformationService = new OrderInformationService(new TaxService());
        $this->paymentService = new BraintreePaymentService(
            $this->gateway,
            $this->orderInformationService,
            $salesChannelConfigService,
            $this->transactionRepository,
            $this->entityManager,
        );
        $this->shop = new ShopEntity('this-is-shop-id', '', 'this-is-shop-secret');
    }

    public function testHandleTransaction(): void
    {
        $paymentMethodNonce = PaymentMethodNonce::factory([
            'threeDSecureInfo' => [
                'status' => ThreeDSecure::STATUS_AUTHENTICATE_SUCCESSFUL,
            ],
            'nonce' => 'this-is-nonce',
            'type' => 1,
        ]);

        $this->paymentMethodNonceGateway
            ->expects(static::once())
            ->method('find')
            ->with('this-is-nonce')
            ->willReturn($paymentMethodNonce);

        $resultSuccess = new Result\Successful([
            'transaction' => Transaction::factory([
                'id' => 'this-is-transaction-id',
                'currencyIsoCode' => 'EUR',
                'amount' => 200,
            ]),
        ], ['transaction']);

        $this->transactionGateway
            ->expects(static::once())
            ->method('sale')
            ->with(static::callback(function (array $sale) {
                static::assertEquals('this-is-merchant-id', $sale['merchantAccountId']);
                static::assertEquals(200, $sale['amount']);
                static::assertEquals(5, $sale['shippingAmount']);
                static::assertEquals(10068, $sale['purchaseOrderNumber']);
                static::assertEquals('this-is-nonce', $sale['paymentMethodNonce']);
                static::assertEquals(20.46, $sale['taxAmount']);
                static::assertEquals(['submitForSettlement' => true], $sale['options']);
                static::assertEquals('this-is-device-data', $sale['deviceData']);
                static::assertEquals('this-is-nonce', $sale['paymentMethodNonce']);
                static::assertEquals('10068', $sale['purchaseOrderNumber']);

                return true;
            }))
            ->willReturn($resultSuccess);

        $paymentPayAction = $this->createPaymentPayAction($this->orderIds, $this->shop, [
            BraintreePaymentService::BRAINTREE_NONCE => 'this-is-nonce',
            BraintreePaymentService::BRAINTREE_DEVICE_DATA => 'this-is-device-data',
        ]);

        $emMatcher = static::exactly(2);
        $this->entityManager
            ->expects($emMatcher)
            ->method('persist')
            ->willReturnCallback(function (object $entity) use (&$emMatcher): void {
                switch ($emMatcher->numberOfInvocations()) {
                    case 1:
                        /** @var TransactionEntity $entity */
                        static::assertInstanceOf(TransactionEntity::class, $entity);
                        static::assertEquals('this-is-transaction-id', $entity->getBraintreeTransactionId());
                        break;
                    case 2:
                        /** @var TransactionReportEntity $entity */
                        static::assertInstanceOf(TransactionReportEntity::class, $entity);
                        static::assertEquals('EUR', $entity->getCurrencyIso());
                        static::assertEquals(200, $entity->getTotalPrice());
                        break;
                }
            });

        $this->entityManager
            ->expects(static::once())
            ->method('flush');

        $transaction = $this->paymentService->handleTransaction($paymentPayAction);

        static::assertEquals('this-is-transaction-id', $transaction->id);
    }

    public function testHandleTransactionWithResultError(): void
    {
        $paymentMethodNonce = PaymentMethodNonce::factory([
            'threeDSecureInfo' => [
                'status' => ThreeDSecure::STATUS_AUTHENTICATE_SUCCESSFUL,
            ],
            'nonce' => 'this-is-nonce',
            'type' => 1,
        ]);

        $this->paymentMethodNonceGateway
            ->expects(static::once())
            ->method('find')
            ->with('this-is-nonce')
            ->willReturn($paymentMethodNonce);

        $resultError = new Result\Error([
            'errors' => [],
            'transaction' => [
                'id' => 'this-is-transaction-id',
            ],
        ]);

        $this->transactionGateway
            ->expects(static::once())
            ->method('sale')
            ->willReturn($resultError);

        $paymentPayAction = $this->createPaymentPayAction($this->orderIds, $this->shop, [BraintreePaymentService::BRAINTREE_NONCE => 'this-is-nonce']);

        static::expectException(BraintreePaymentException::class);
        static::expectExceptionMessage('Braintree payment process failed: ');

        $this->paymentService->handleTransaction($paymentPayAction);
    }

    public function testHandleTransactionWithoutTransaction(): void
    {
        $paymentMethodNonce = PaymentMethodNonce::factory([
            'threeDSecureInfo' => [
                'status' => ThreeDSecure::STATUS_AUTHENTICATE_SUCCESSFUL,
            ],
            'nonce' => 'this-is-nonce',
            'type' => 1,
        ]);

        $this->paymentMethodNonceGateway
            ->expects(static::once())
            ->method('find')
            ->with('this-is-nonce')
            ->willReturn($paymentMethodNonce);

        $resultSuccess = new Result\Successful([], []);

        $this->transactionGateway
            ->expects(static::once())
            ->method('sale')
            ->willReturn($resultSuccess);

        $paymentPayAction = $this->createPaymentPayAction($this->orderIds, $this->shop, [BraintreePaymentService::BRAINTREE_NONCE => 'this-is-nonce']);

        static::expectException(BraintreePaymentException::class);
        static::expectExceptionMessage('Braintree payment process failed: No transaction provided');

        $this->paymentService->handleTransaction($paymentPayAction);
    }

    public function testHandleTransactionWith3DSFailed(): void
    {
        $paymentMethodNonce = PaymentMethodNonce::factory([
            'threeDSecureInfo' => [
                'status' => ThreeDSecure::STATUS_AUTHENTICATE_FAILED,
            ],
            'nonce' => 'this-is-nonce',
            'type' => 1,
        ]);

        $this->paymentMethodNonceGateway
            ->expects(static::once())
            ->method('find')
            ->with('this-is-nonce')
            ->willReturn($paymentMethodNonce);

        $this->transactionGateway
            ->expects(static::never())
            ->method('sale');

        $paymentPayAction = $this->createPaymentPayAction($this->orderIds, $this->shop, [BraintreePaymentService::BRAINTREE_NONCE => 'this-is-nonce']);

        static::expectException(BraintreePaymentException::class);
        static::expectExceptionMessage('Braintree payment process failed: 3D secure validation failed');

        $this->paymentService->handleTransaction($paymentPayAction);
    }

    public function testHandleTransactionWithout3DS(): void
    {
        $paymentMethodNonce = PaymentMethodNonce::factory([
            'threeDSecureInfo' => null,
            'nonce' => 'this-is-nonce',
            'type' => 1,
        ]);

        $this->paymentMethodNonceGateway
            ->expects(static::once())
            ->method('find')
            ->with('this-is-nonce')
            ->willReturn($paymentMethodNonce);

        $this->transactionGateway
            ->expects(static::never())
            ->method('sale');

        $paymentPayAction = $this->createPaymentPayAction($this->orderIds, $this->shop, [BraintreePaymentService::BRAINTREE_NONCE => 'this-is-nonce']);

        static::expectException(BraintreePaymentException::class);
        static::expectExceptionMessage('Braintree payment process failed: 3D secure validation failed');

        $this->paymentService->handleTransaction($paymentPayAction);
    }

    public function testHandleTransactionWith3DSThrowsException(): void
    {
        $this->paymentMethodNonceGateway
            ->expects(static::once())
            ->method('find')
            ->with('this-is-nonce')
            ->willThrowException(new \Exception());

        $this->transactionGateway
            ->expects(static::never())
            ->method('sale');

        $paymentPayAction = $this->createPaymentPayAction($this->orderIds, $this->shop, [BraintreePaymentService::BRAINTREE_NONCE => 'this-is-nonce']);

        static::expectException(BraintreePaymentException::class);
        static::expectExceptionMessage('Braintree payment process failed: 3D secure validation failed');

        $this->paymentService->handleTransaction($paymentPayAction);
    }

    public function testHandleTransactionWithoutMerchantIdThrowsException(): void
    {
        $salesChannelConfigService = $this->createMock(SalesChannelConfigService::class);
        $salesChannelConfigService->method('getMerchantId')->willReturn(null);

        $this->paymentService = new BraintreePaymentService(
            $this->gateway,
            $this->orderInformationService,
            $salesChannelConfigService,
            $this->transactionRepository,
            $this->entityManager,
        );

        $this->paymentMethodNonceGateway
            ->expects(static::never())
            ->method('find');

        $this->transactionGateway
            ->expects(static::never())
            ->method('sale');

        $paymentPayAction = $this->createPaymentPayAction($this->orderIds, $this->shop, [BraintreePaymentService::BRAINTREE_NONCE => 'this-is-nonce']);

        static::expectException(BraintreePaymentException::class);
        static::expectExceptionMessage('Braintree payment process failed: Braintree is not supported for the selected currency');

        $this->paymentService->handleTransaction($paymentPayAction);
    }

    public function testExtractNonce(): void
    {
        $paymentPayAction = $this->createPaymentPayAction($this->orderIds, $this->shop, [BraintreePaymentService::BRAINTREE_NONCE => 'this-is-nonce']);

        $nonce = $this->paymentService->extractNonce($paymentPayAction);

        static::assertSame('this-is-nonce', $nonce);
    }

    public function testExtractNonceWithoutRequestData(): void
    {
        $paymentPayAction = $this->createPaymentPayAction($this->orderIds, $this->shop);

        static::expectException(\RuntimeException::class);

        $this->paymentService->extractNonce($paymentPayAction);
    }

    public function testExtractNonceWithoutNonceKey(): void
    {
        $paymentPayAction = $this->createPaymentPayAction($this->orderIds, $this->shop, ['foo' => 'bar']);

        static::expectException(BraintreePaymentException::class);

        $this->paymentService->extractNonce($paymentPayAction);
    }

    public function testExtractNonceWithNonStringNonce(): void
    {
        $paymentPayAction = $this->createPaymentPayAction($this->orderIds, $this->shop, [BraintreePaymentService::BRAINTREE_NONCE => 123]);

        static::expectException(\RuntimeException::class);

        $this->paymentService->extractNonce($paymentPayAction);
    }

    public function testGetTransactionDetails(): void
    {
        $transactionEntity = (new TransactionEntity())
            ->setBraintreeTransactionId('this-is-transaction-id')
            ->setOrderTransactionId('this-is-order-transaction-id');

        $this->transactionRepository
            ->expects(static::once())
            ->method('findNewestBraintreeTransaction')
            ->with($this->shop, ['this-is-order-transaction-id'])
            ->willReturn($transactionEntity);

        $transaction = Transaction::factory([]);

        $this->transactionGateway
            ->expects(static::once())
            ->method('find')
            ->with('this-is-transaction-id')
            ->willReturn($transaction);

        $transactionDetails = $this->paymentService->getTransactionDetails($this->shop, ['this-is-order-transaction-id']);
        static::assertSame($transaction, $transactionDetails);
    }

    public function testGetTransactionDetailsWithoutBraintreeTransaction(): void
    {
        $transactionEntity = (new TransactionEntity())
            ->setBraintreeTransactionId('this-is-transaction-id')
            ->setOrderTransactionId('this-is-order-transaction-id');

        $this->transactionRepository
            ->expects(static::once())
            ->method('findNewestBraintreeTransaction')
            ->with($this->shop, ['this-is-order-transaction-id'])
            ->willReturn($transactionEntity);

        $this->transactionGateway
            ->expects(static::once())
            ->method('find')
            ->with('this-is-transaction-id')
            ->willThrowException(new NotFound());

        static::expectException(BraintreePaymentException::class);
        static::expectExceptionMessage('No braintree transaction found');

        $this->paymentService->getTransactionDetails($this->shop, ['this-is-order-transaction-id']);
    }

    public function testGetTransactionDetailsWithoutTransactionEntity(): void
    {
        $this->transactionRepository
            ->expects(static::once())
            ->method('findNewestBraintreeTransaction')
            ->with($this->shop, ['this-is-order-transaction-id'])
            ->willReturn(null);

        $this->transactionGateway
            ->expects(static::never())
            ->method('find');

        static::expectException(BraintreePaymentException::class);
        static::expectExceptionMessage('No braintree transaction found');

        $this->paymentService->getTransactionDetails($this->shop, ['this-is-order-transaction-id']);
    }
}
