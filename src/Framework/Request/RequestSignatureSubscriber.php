<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\Request;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestSignatureSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onRequest'];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->isMethod(Request::METHOD_GET)) {
            return;
        }

        if (!$request->query->has('shopware-shop-signature')) {
            return;
        }

        $params = $request->query->all();
        $newParams = [];

        foreach ($params as $key => $param) {
            if ($key === 'shop-url') {
                $newParams[] = \sprintf('%s=%s', $key, $param);
                continue;
            }

            $newParams[] = \sprintf('%s=%s', $key, \rawurlencode($param));
        }

        $query = \implode('&', $newParams);

        $request->server->set('QUERY_STRING', $query);
    }
}
