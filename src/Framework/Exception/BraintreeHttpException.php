<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @phpstan-type ErrorData array{status: string, code: string, title: string, detail: string, meta: array{parameters: array<string, mixed>}, trace?: array<int, mixed>}
 */
abstract class BraintreeHttpException extends HttpException
{
    /**
     * @var mixed[]
     */
    private array $parameters = [];

    /**
     * @param mixed[] $parameters
     */
    public function __construct(
        string $message,
        array $parameters = [],
        \Throwable $e = null
    ) {
        $this->parameters = $parameters;
        $message = $this->parse($message, $parameters);

        parent::__construct($this->getStatusCode(), $message, $e);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function getErrors(bool $withTrace = false): \Generator
    {
        yield $this->getCommonErrorData($withTrace);
    }

    /**
     * @return mixed[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getParameter(string $key): mixed
    {
        return $this->parameters[$key] ?? null;
    }

    /**
     * @infection-ignore-all false positive - not testable either
     *
     * @return ErrorData
     */
    protected function getCommonErrorData(bool $withTrace = false): array
    {
        $error = [
            'status' => (string) $this->getStatusCode(),
            'code' => $this->getErrorCode(),
            'title' => Response::$statusTexts[$this->getStatusCode()] ?? 'unknown status',
            'detail' => $this->getMessage(),
            'meta' => [
                'parameters' => $this->getParameters(),
            ],
        ];

        if ($withTrace) {
            $error['trace'] = $this->getTrace();
        }

        return $error;
    }

    /**
     * @param mixed[] $parameters
     */
    protected function parse(string $message, array $parameters = []): string
    {
        $regex = [];

        foreach ($parameters as $key => $value) {
            if (\is_array($value)) {
                continue;
            }

            /** @infection-ignore-all */
            $formattedKey = preg_replace('/[^a-z]/i', '', $key);

            /** @infection-ignore-all */
            $regex[sprintf('/\{\{(\s+)?(%s)(\s+)?\}\}/', $formattedKey)] = $value;
        }

        /** @infection-ignore-all */
        return (string) preg_replace(array_keys($regex), array_values($regex), $message);
    }

    abstract public function getErrorCode(): string;
}
