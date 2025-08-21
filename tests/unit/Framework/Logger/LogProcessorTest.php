<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Framework\Logger;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Order\Order;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Framework\Collection;
use Shopware\AppBundle\AppRequest;
use Swag\Braintree\Braintree\Exception\BraintreePaymentException;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Framework\Logger\LogProcessor;
use Swag\Braintree\Framework\Request\RequestDebugIdSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(LogProcessor::class)]
class LogProcessorTest extends TestCase
{
    private RequestStack $requestStack;

    private LogProcessor&MockObject $processor;

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->with('request_stack', ContainerInterface::NULL_ON_INVALID_REFERENCE)
            ->willReturn($this->requestStack);

        $this->processor = $this->getMockBuilder(LogProcessor::class)
            ->onlyMethods(['getBacktrace'])
            ->setConstructorArgs([$container])
            ->getMock();
    }

    public function testInvokeWithException(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new \Exception('Test exception', previous: $previous);

        $record = $this->getLogRecord('Test message with exception', [LogProcessor::EXCEPTION => $exception]);
        $result = ($this->processor)($record);

        static::assertEquals(['exception' => [
            'message' => 'Test exception',
            'class' => \Exception::class,
            'tracedAt' => self::class . '->' . __FUNCTION__,
            'thrownAt' => $exception->getFile() . '#L' . $exception->getLine(),
            'previous' => [
                'message' => 'Previous exception',
                'class' => \Exception::class,
                'tracedAt' => self::class . '->' . __FUNCTION__,
                'thrownAt' => $previous->getFile() . '#L' . $previous->getLine(),
            ],
        ]], $result->context);
        static::assertSame([], $result->extra);
    }

    public function testInvokeWithBraintreeHttpException(): void
    {
        $exception = new BraintreePaymentException('Test Braintree exception', transactionId: 'test-transaction-id');

        $record = $this->getLogRecord('Test message with Braintree exception', [LogProcessor::EXCEPTION => $exception]);
        $result = ($this->processor)($record);

        try {
            throw $exception;
        } catch (\Throwable $e) {
            $exception = $e;
        }

        static::assertEquals(['exception' => [
            'message' => 'Braintree payment process failed: Test Braintree exception',
            'class' => BraintreePaymentException::class,
            'tracedAt' => self::class . '->' . __FUNCTION__,
            'thrownAt' => $exception->getFile() . '#L' . $exception->getLine(),
            'parameters' => ['transactionId' => 'test-transaction-id'],
            'errorCode' => BraintreePaymentException::ERROR_CODE,
            'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR,
        ]], $result->context);
        static::assertSame([], $result->extra);
    }

    public function testInvokeWithAction(): void
    {
        $action = new PaymentPayAction(
            new ShopEntity('shop-id', 'shop-url', 'shop-secret'),
            new ActionSource('this-is-url', '1.0.0', new Collection()),
            new Order(['id' => 'order-id', 'orderNumber' => 'SW-1000']),
            new OrderTransaction(['id' => 'order-transaction-id']),
        );

        $record = $this->getLogRecord('Test message with action', [LogProcessor::ACTION => $action]);
        $result = ($this->processor)($record);

        static::assertEquals(['action' => [
            'appVersion' => '1.0.0',
            'order' => [
                'id' => 'order-id',
                'transactionId' => 'order-transaction-id',
                'number' => 'SW-1000',
            ],
        ]], $result->context);
        static::assertSame([], $result->extra);
    }

    public function testInvokeWithWrongAction(): void
    {
        $action = new \stdClass();

        $record = $this->getLogRecord('Test message with action', [LogProcessor::ACTION => $action]);
        $result = ($this->processor)($record);

        static::assertEquals(['action' => []], $result->context);
        static::assertSame([], $result->extra);
    }

    public function testInvokeWithRequest(): void
    {
        $request = new Request();
        $request->attributes->set('_route', 'some.route');
        $request->attributes->set('_controller', '\Controller');
        $request->attributes->set(AppRequest::SIGN_RESPONSE, true);
        $request->attributes->set(RequestDebugIdSubscriber::DEBUG_ID_ATTRIBUTE, 'debug-id');

        $this->requestStack->push($request);

        $record = $this->getLogRecord('Test message with request');
        $result = ($this->processor)($record);

        static::assertEquals(['request' => [
            'route' => 'some.route',
            'controller' => '\Controller',
            'signed_response' => true,
        ]], $result->extra);
        static::assertSame(['debugId' => 'debug-id'], $result->context);
    }

    public function testInvokeWithEmptyRequest(): void
    {
        $request = new Request();
        $this->requestStack->push($request);

        $record = $this->getLogRecord('Test message with request');
        $result = ($this->processor)($record);

        static::assertEquals(['request' => [
            'route' => null,
            'controller' => null,
            'signed_response' => false,
        ]], $result->extra);
        static::assertSame(['debugId' => null], $result->context);
    }

    public function testInvokeWithShop(): void
    {
        $shop = new ShopEntity('shop-id', 'shop-url', 'shop-secret');

        $request = new Request();
        $request->attributes->set(AppRequest::SHOP_ATTRIBUTE, $shop);

        $this->requestStack->push($request);

        $record = $this->getLogRecord('Test message with shop');
        $result = ($this->processor)($record);

        static::assertSame(['debugId' => null, 'shopId' => 'shop-id'], $result->context);
    }

    public function testInvokeWithBacktrace(): void
    {
        $backtrace = [
            ['file' => 'file1.php', 'line' => 1, 'class' => 'class1', 'function' => 'call_user_func'],
            ['file' => 'file2.php', 'line' => 2, 'class' => 'class2', 'function' => 'function1'],
            ['file' => 'file3.php', 'line' => 3, 'class' => 'class3', 'function' => 'function2'],
        ];

        $this->processor->expects(static::once())
            ->method('getBacktrace')
            ->willReturn($backtrace);

        $record = $this->getLogRecord('Test message with backtrace');
        $result = ($this->processor)($record);

        static::assertSame([], $result->context);
        static::assertEquals([
            'loggedAt' => 'class2::function1',
        ], $result->extra);
    }

    public function testRecordIsMerged(): void
    {
        $request = new Request();
        $this->requestStack->push($request);

        $record = $this->getLogRecord('Test message', ['context-key' => 'context-value'], ['extra-key' => 'extra-value']);
        $result = ($this->processor)($record);

        static::assertSame([
            'context-key' => 'context-value',
            'debugId' => null,
        ], $result->context);
        static::assertEquals([
            'extra-key' => 'extra-value',
            'request' => [
                'route' => null,
                'controller' => null,
                'signed_response' => false,
            ],
        ], $result->extra);
    }

    /**
     * @param array<string, string> $trace
     */
    #[DataProvider('traceToClassString')]
    public function testTraceToClassString(string $expected, array $trace): void
    {
        $this->processor->expects(static::once())
            ->method('getBacktrace')
            ->willReturn([$trace]);

        $record = $this->getLogRecord('Test message with backtrace');
        $result = ($this->processor)($record);

        static::assertSame([], $result->context);
        static::assertEquals(['loggedAt' => $expected], $result->extra);
    }

    public static function traceToClassString(): \Generator
    {
        yield 'complete' => ['class->function', ['file' => 'file.php', 'class' => 'class', 'type' => '->', 'function' => 'function']];
        yield 'missing type' => ['class::function', ['file' => 'file.php', 'class' => 'class', 'function' => 'function']];
        yield 'missing class' => ['file.php::function', ['file' => 'file.php', 'function' => 'function']];
        yield 'missing file' => ['class::function', ['class' => 'class', 'function' => 'function']];
        yield 'missing function' => ['class::', ['file' => 'file.php', 'class' => 'class']];
    }

    /**
     * @param array<mixed> $context
     * @param array<mixed> $extra
     */
    private function getLogRecord(string $message, array $context = [], array $extra = []): LogRecord
    {
        return new LogRecord(
            new \DateTimeImmutable(),
            'app',
            Level::Notice,
            $message,
            $context,
            $extra
        );
    }
}
