<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Framework\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Framework\Exception\BraintreeHttpException;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(BraintreeHttpException::class)]
class BraintreeHttpExceptionTest extends TestCase
{
    public function testException(): void
    {
        $previous = new \RuntimeException();

        $e = $this->getException('test message', [], $previous, 'FOO_BAR');

        static::assertSame('test message', $e->getMessage());
        static::assertSame([], $e->getParameters());
        static::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getStatusCode());
        static::assertSame('FOO_BAR', $e->getErrorCode());
        static::assertSame($previous, $e->getPrevious());
    }

    public function testExceptionWithMessageParameters(): void
    {
        $e = $this->getException(
            'test message {{ test }}: {{ message }}',
            ['test' => 'ohoh', 'message' => 'something went wrong']
        );

        static::assertSame('test message ohoh: something went wrong', $e->getMessage());
        static::assertSame(['test' => 'ohoh', 'message' => 'something went wrong'], $e->getParameters());
        static::assertSame('ohoh', $e->getParameter('test'));
        static::assertSame('something went wrong', $e->getParameter('message'));
        static::assertNull($e->getParameter('not-existing'));
    }

    public function testExceptionWithArrayMessageParameters(): void
    {
        $e = $this->getException(
            'test message {{ test }}: {{ message }}',
            ['test' => ['ohoh'], 'message' => 'something went wrong']
        );

        static::assertSame('test message {{ test }}: something went wrong', $e->getMessage());
        static::assertSame(['test' => ['ohoh'], 'message' => 'something went wrong'], $e->getParameters());
        static::assertSame(['ohoh'], $e->getParameter('test'));
        static::assertSame('something went wrong', $e->getParameter('message'));
        static::assertNull($e->getParameter('not-existing'));
    }

    public function testCommonErrorData(): void
    {
        $e = $this->getException('test message', ['foo' => 'bar']);

        $errors = $e->getErrors(true);
        static::assertIsIterable($errors);

        $errors = \iterator_to_array($errors);
        static::assertCount(1, $errors);

        $error = $errors[0];

        static::assertSame(['status', 'code', 'title', 'detail', 'meta', 'trace'], \array_keys($error));

        static::assertSame((string) Response::HTTP_INTERNAL_SERVER_ERROR, $error['status']);
        static::assertSame('SWAG_BRAINTREE__TEST', $error['code']);
        static::assertSame('Internal Server Error', $error['title']);
        static::assertSame('test message', $error['detail']);
        static::assertSame(['parameters' => ['foo' => 'bar']], $error['meta']);
        static::assertSame($e->getTrace(), $error['trace']);
    }

    public function testCommonErrorDataWithoutTrace(): void
    {
        $e = $this->getException('test message', ['foo' => 'bar']);

        $errors = $e->getErrors();
        static::assertIsIterable($errors);

        $errors = \iterator_to_array($errors);
        static::assertCount(1, $errors);

        $error = $errors[0];

        static::assertSame(['status', 'code', 'title', 'detail', 'meta'], \array_keys($error));
    }

    public function testCommonErrorDataDefault(): void
    {
        $e = $this->getException('test message', ['foo' => 'bar']);

        $error = $e->testCommonErrorData();
        static::assertSame(['status', 'code', 'title', 'detail', 'meta'], \array_keys($error));

        $error = $e->testCommonErrorData(true);
        static::assertSame(['status', 'code', 'title', 'detail', 'meta', 'trace'], \array_keys($error));
    }

    public function testParseIsAvailable(): void
    {
        $e = $this->getException('test message', ['bar' => 'bar']);
        $str = $e->testParse('foo {{ bar }}', ['bar' => 'bar']);

        static::assertSame('foo bar', $str);
    }

    public function testCommonErrorDataIsAvailable(): void
    {
        $e = $this->getException('test message', ['bar' => 'bar']);
        $e->testCommonErrorData(true);
        $e->testCommonErrorData(false);

        static::expectNotToPerformAssertions();
    }

    /**
     * @param mixed[] $parameters
     */
    private function getException(
        string $message,
        array $parameters = [],
        \Throwable $previous = null,
        string $errorCode = 'SWAG_BRAINTREE__TEST',
    ): TestException {
        return new TestException($message, $parameters, $previous, $errorCode);
    }
}

class TestException extends BraintreeHttpException
{
    private readonly string $errorCode;

    public function __construct(
        string $message,
        array $parameters = [],
        \Throwable $e = null,
        string $errorCode = 'SWAG_BRAINTREE__TEST',
    ) {
        $this->errorCode = $errorCode;

        parent::__construct($message, $parameters, $e);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @param mixed[] $parameters
     */
    public function testParse(string $message, array $parameters = []): string
    {
        return $this->parse($message, $parameters);
    }

    /**
     * @return mixed[]
     */
    public function testCommonErrorData(bool $withTrace = false): array
    {
        return $this->getCommonErrorData($withTrace);
    }
}
