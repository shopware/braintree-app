<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Controller;

use Braintree\Transaction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Swag\Braintree\Braintree\Exception\BraintreePaymentException;
use Swag\Braintree\Braintree\Payment\BraintreePaymentService;
use Swag\Braintree\Controller\PaymentController;
use Swag\Braintree\Entity\ShopEntity;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(PaymentController::class)]
class PaymentControllerTest extends TestCase
{
    private PaymentController $paymentController;

    private MockObject&HttpFoundationFactoryInterface $httpFoundationFactory;

    private MockObject&BraintreePaymentService $paymentService;

    protected function setUp(): void
    {
        $this->httpFoundationFactory = $this->createMock(HttpFoundationFactoryInterface::class);
        $this->paymentService = $this->createMock(BraintreePaymentService::class);

        $this->paymentController = new PaymentController(
            $this->paymentService,
            $this->httpFoundationFactory,
        );
    }

    public function testPaySuccess(): void
    {
        $payment = $this->createMock(PaymentPayAction::class);

        $this->paymentService->expects(static::once())
            ->method('handleTransaction')
            ->with($payment);

        $this->httpFoundationFactory
            ->expects(static::once())
            ->method('createResponse')
            ->willReturnCallback(function (ResponseInterface $response) {
                $body = \json_decode($response->getBody()->getContents(), true);
                static::assertNotNull($body);
                static::assertSame('paid', $body['status']);

                return new Response();
            });

        $this->paymentController->pay($payment);
    }

    public function testPayFailure(): void
    {
        $payment = $this->createMock(PaymentPayAction::class);

        $this->paymentService->expects(static::once())
            ->method('handleTransaction')
            ->with($payment)
            ->willThrowException(new BraintreePaymentException('this-is-failed-msg'));

        $this->httpFoundationFactory
            ->expects(static::once())
            ->method('createResponse')
            ->willReturnCallback(function (ResponseInterface $response) {
                $body = \json_decode($response->getBody()->getContents(), true);
                static::assertNotNull($body);
                static::assertSame('fail', $body['status']);
                static::assertStringContainsString('this-is-failed-msg', $body['message']);

                return new Response();
            });

        $this->paymentController->pay($payment);
    }

    public function testFindBraintreeTransactionForOrderTransactions(): void
    {
        $data = [
            'transactions' => ['transaction-id'],
        ];

        $shop = new ShopEntity('', '', '');

        $request = new Request(content: \json_encode($data));

        $this->paymentService
            ->expects(static::once())
            ->method('getTransactionDetails')
            ->with($shop, $data['transactions'])
            ->willReturn(Transaction::factory([]));

        $this->paymentController->findBraintreeTransactionForOrderTransactions($request, $shop);
    }

    public function testFindBraintreeTransactionForOrderTransactionsWithoutArray(): void
    {
        $data = 'transaction-id';

        $shop = new ShopEntity('', '', '');

        $request = new Request(content: \json_encode($data));

        $this->paymentService
            ->expects(static::never())
            ->method('getTransactionDetails');

        static::assertNull($this->paymentController->findBraintreeTransactionForOrderTransactions($request, $shop));
    }

    public function testFindBraintreeTransactionForOrderTransactionsWithoutIds(): void
    {
        $data = [];

        $shop = new ShopEntity('', '', '');

        $request = new Request(content: \json_encode($data));

        $this->paymentService
            ->expects(static::never())
            ->method('getTransactionDetails');

        static::assertNull($this->paymentController->findBraintreeTransactionForOrderTransactions($request, $shop));
    }
}
