<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Framework\Request;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Framework\Request\RequestDebugIdSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[CoversClass(RequestDebugIdSubscriber::class)]
class RequestDebugIdSubscriberTest extends TestCase
{
    private RequestStack $requestStack;

    private RequestDebugIdSubscriber $subsciber;

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();
        $this->subsciber = new RequestDebugIdSubscriber($this->requestStack);
    }

    public function testSubscribedEvents(): void
    {
        static::assertSame([
            KernelEvents::REQUEST => ['onRequest', 10000],
            KernelEvents::RESPONSE => 'onResponse',
        ], $this->subsciber->getSubscribedEvents());
    }

    public function testOnRequest(): void
    {
        $request = new Request();

        $this->subsciber->onRequest(new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        ));

        $debugId = $request->attributes->get(RequestDebugIdSubscriber::DEBUG_ID_ATTRIBUTE);

        static::assertIsString($debugId);
        static::assertSame(10, \strlen($debugId));
    }

    public function testOnRequestWithSubrequest(): void
    {
        $debugId = 'this-is-debug-id';

        $mainRequest = new Request();
        $mainRequest->attributes->set(RequestDebugIdSubscriber::DEBUG_ID_ATTRIBUTE, $debugId);

        $this->requestStack->push($mainRequest);

        $subRequest = new Request();

        $this->subsciber->onRequest(new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $subRequest,
            HttpKernelInterface::SUB_REQUEST,
        ));

        static::assertSame($debugId, $subRequest->attributes->get(RequestDebugIdSubscriber::DEBUG_ID_ATTRIBUTE));
    }

    public function testOnRequestWithSubrequestWithoutMain(): void
    {
        $subRequest = new Request();

        $this->subsciber->onRequest(new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $subRequest,
            HttpKernelInterface::SUB_REQUEST,
        ));

        $debugId = $subRequest->attributes->get(RequestDebugIdSubscriber::DEBUG_ID_ATTRIBUTE);

        static::assertIsString($debugId);
        static::assertSame(10, \strlen($debugId));
    }

    public function testOnResponse(): void
    {
        $debugId = 'this-is-debug-id';

        $request = new Request();
        $request->attributes->set(RequestDebugIdSubscriber::DEBUG_ID_ATTRIBUTE, $debugId);

        $response = new Response();

        $this->subsciber->onResponse(new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        ));

        static::assertSame($debugId, $response->headers->get(RequestDebugIdSubscriber::DEBUG_ID_HEADER));
    }

    public function testOnResponseWithoutIdInRequest(): void
    {
        $request = new Request();
        $response = new Response();

        $this->subsciber->onResponse(new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        ));

        $debugId = $response->headers->get(RequestDebugIdSubscriber::DEBUG_ID_HEADER);

        static::assertIsString($debugId);
        static::assertSame(10, \strlen($debugId));
    }
}
