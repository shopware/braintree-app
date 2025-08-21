<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\Request;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestDebugIdSubscriber implements EventSubscriberInterface
{
    public const DEBUG_ID_ATTRIBUTE = '_debug_id';
    public const DEBUG_ID_HEADER = 'x-debug-id';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 10000],
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            $debugId = $this->requestStack->getMainRequest()?->attributes->get(self::DEBUG_ID_ATTRIBUTE);
        }

        $debugId ??= \bin2hex(\random_bytes(5));

        $event->getRequest()->attributes->set(self::DEBUG_ID_ATTRIBUTE, $debugId);
    }

    public function onResponse(ResponseEvent $event): void
    {
        $debugId = $event->getRequest()->attributes->get(self::DEBUG_ID_ATTRIBUTE, \bin2hex(\random_bytes(5)));

        $event->getResponse()->headers->set(self::DEBUG_ID_HEADER, $debugId);
    }
}
