<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Framework\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Framework\Response\ResponseSerializerSubscriber;
use Swag\Braintree\Framework\Serializer\EntityNormalizer;
use Swag\Braintree\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(ResponseSerializerSubscriber::class)]
class ResponseSerializerSubscriberTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        static::assertSame(
            [KernelEvents::VIEW => 'serializeResponse'],
            ResponseSerializerSubscriber::getSubscribedEvents()
        );
    }

    public function testSerializeResponse(): void
    {
        $request = new Request();
        $result = new \stdClass();
        $result->foo = 'bar';

        $event = new ViewEvent(
            new Kernel('test', true),
            $request,
            Kernel::MAIN_REQUEST,
            $result
        );

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer
            ->expects(static::once())
            ->method('serialize')
            ->with($result, 'json', [
                EntityNormalizer::ORIGINAL_DATA => $result,
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['__initializer__', '__cloner__', '__isInitialized__'],
            ])
            ->willReturn('{"foo":"bar"}');

        $subscriber = new ResponseSerializerSubscriber($serializer);
        $subscriber->serializeResponse($event);

        $response = $event->getResponse();

        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
        static::assertSame('{"foo":"bar"}', $response->getContent());
        static::assertSame('application/json', $response->headers->get('Content-Type'));
    }

    public function testSerializeResponseWithPsr7Response(): void
    {
        $request = new Request();
        $result = new \GuzzleHttp\Psr7\Response();

        $event = new ViewEvent(
            new Kernel('test', true),
            $request,
            Kernel::MAIN_REQUEST,
            $result
        );

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer
            ->expects(static::never())
            ->method('serialize');

        $subscriber = new ResponseSerializerSubscriber($serializer);
        $subscriber->serializeResponse($event);

        static::assertNull($event->getResponse());
    }
}
