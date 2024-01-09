<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Framework\Request;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Framework\Request\RequestSignatureSubscriber;
use Swag\Braintree\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[CoversClass(RequestSignatureSubscriber::class)]
class RequestSignatureSubscriberTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        static::assertSame(
            [KernelEvents::REQUEST => 'onRequest'],
            RequestSignatureSubscriber::getSubscribedEvents()
        );
    }

    public function testOnRequest(): void
    {
        $request = new Request(
            query: ['shopware-shop-signature' => 'signature', 'shop-url' => 'http://foo.bar', 'foo' => 'i:will/be&encoded'],
            server: ['REQUEST_METHOD' => Request::METHOD_GET],
        );

        $event = new RequestEvent(
            new Kernel('test', true),
            $request,
            Kernel::MAIN_REQUEST
        );

        $subscriber = new RequestSignatureSubscriber();
        $subscriber->onRequest($event);

        $newRequest = $event->getRequest();

        static::assertTrue($newRequest->server->has('QUERY_STRING'));

        $queryString = $newRequest->server->get('QUERY_STRING');

        static::assertSame(
            'shopware-shop-signature=signature&shop-url=http://foo.bar&foo=i%3Awill%2Fbe%26encoded',
            $queryString
        );
    }

    public function testOnRequestWithPostMethod(): void
    {
        $request = new Request(
            query: ['shopware-shop-signature' => 'signature', 'shop-url' => 'http://foo.bar', 'foo' => 'i:will/be&encoded'],
            server: ['REQUEST_METHOD' => Request::METHOD_POST, 'QUERY_STRING' => 'foo'],
        );

        $event = new RequestEvent(
            new Kernel('test', true),
            $request,
            Kernel::MAIN_REQUEST
        );

        $subscriber = new RequestSignatureSubscriber();
        $subscriber->onRequest($event);

        $newRequest = $event->getRequest();

        static::assertTrue($newRequest->server->has('QUERY_STRING'));

        $queryString = $newRequest->server->get('QUERY_STRING');

        static::assertSame('foo', $queryString);
    }

    public function testOnRequestWithoutShopwareSignature(): void
    {
        $request = new Request(
            query: ['shop-url' => 'http://foo.bar', 'foo' => 'i:will/be&encoded'],
            server: ['REQUEST_METHOD' => Request::METHOD_GET, 'QUERY_STRING' => 'foo'],
        );

        $event = new RequestEvent(
            new Kernel('test', true),
            $request,
            Kernel::MAIN_REQUEST
        );

        $subscriber = new RequestSignatureSubscriber();
        $subscriber->onRequest($event);

        $newRequest = $event->getRequest();

        static::assertTrue($newRequest->server->has('QUERY_STRING'));

        $queryString = $newRequest->server->get('QUERY_STRING');

        static::assertSame('foo', $queryString);
    }
}
