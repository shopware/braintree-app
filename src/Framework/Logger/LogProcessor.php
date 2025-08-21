<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\Logger;

use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\AppBundle\AppRequest;
use Swag\Braintree\Framework\Exception\BraintreeHttpException;
use Swag\Braintree\Framework\Request\RequestDebugIdSubscriber;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @phpstan-type Trace array{file?: string, line?: int, function?: string, class?: string, type?: string}
 */
#[AsMonologProcessor]
class LogProcessor
{
    public const EXCEPTION = 'exception';

    public const ACTION = 'action';

    private const SKIP_FUNCTIONS = [
        'call_user_func',
        'call_user_func_array',
    ];

    public function __construct(
        #[Autowire(service: 'service_container')]
        private readonly ContainerInterface $container,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $request = $this->container->get('request_stack', ContainerInterface::NULL_ON_INVALID_REFERENCE)->getCurrentRequest();
        /** @var ShopInterface|null $shop */
        $shop = $request?->attributes->get(AppRequest::SHOP_ATTRIBUTE);

        $context = [];
        $extra = [];

        if ($request) {
            $extra['request'] = [];
            $extra['request']['route'] = $request->attributes->get('_route');
            $extra['request']['controller'] = $request->attributes->get('_controller');
            $extra['request']['signed_response'] = $request->attributes->get(AppRequest::SIGN_RESPONSE, false);
            $context['debugId'] = $request->attributes->get(RequestDebugIdSubscriber::DEBUG_ID_ATTRIBUTE);
        }

        if ($shop) {
            $context['shopId'] = $shop->getShopId();
        }

        if (($record->context[self::EXCEPTION] ?? null) instanceof \Throwable) {
            $context[self::EXCEPTION] = $this->exceptionToContext($record->context[self::EXCEPTION]);
        }

        if (\is_object($record->context[self::ACTION] ?? null)) {
            $context[self::ACTION] = $this->actionToContext($record->context[self::ACTION]);
        }

        foreach ($this->getBacktrace() as $trace) {
            if (\in_array($trace['function'] ?? null, self::SKIP_FUNCTIONS, true)) {
                continue;
            }

            $extra['loggedAt'] = $this->traceToClassString($trace);

            break;
        }

        return $record->with(...[
            'context' => [...$record->context, ...$context],
            'extra' => [...$record->extra, ...$extra],
        ]);
    }

    /**
     * @return Trace[]
     */
    protected function getBacktrace(): array
    {
        $traces = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);

        // remove getBacktrace(), __invoke(), 2x logger call
        \array_splice($traces, 0, 3);

        return $traces;
    }

    /**
     * @return array<string, mixed>
     */
    private function actionToContext(object $action): array
    {
        $context = [];

        if (\property_exists($action, 'source') && $action->source instanceof ActionSource) {
            $context['appVersion'] = $action->source->appVersion;
        }

        if ($action instanceof PaymentPayAction) {
            $context['order'] = [
                'id' => $action->order->getId(),
                'transactionId' => $action->orderTransaction->getId(),
                'number' => $action->order->getOrderNumber(),
            ];
        }

        return $context;
    }

    /**
     * @return array<string, mixed>
     */
    private function exceptionToContext(\Throwable $exception): array
    {
        $context = [
            'message' => $exception->getMessage(),
            'class' => $exception::class,
            'tracedAt' => $this->traceToClassString($exception->getTrace()[0]),
            'thrownAt' => $exception->getFile() . '#L' . $exception->getLine(),
        ];

        if ($exception instanceof BraintreeHttpException) {
            $context['parameters'] = $exception->getParameters();
            $context['errorCode'] = $exception->getErrorCode();
        }

        if ($exception instanceof HttpException) {
            $context['statusCode'] = $exception->getStatusCode();
        }

        if ($exception->getPrevious()) {
            $context['previous'] = $this->exceptionToContext($exception->getPrevious());
        }

        return $context;
    }

    /**
     * @param Trace $trace
     */
    private function traceToClassString(array $trace): string
    {
        return ($trace['class'] ?? $trace['file'] ?? '') . ($trace['type'] ?? '::') . ($trace['function'] ?? '');
    }
}
