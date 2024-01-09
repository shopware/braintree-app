<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\Response;

use Psr\Http\Message\ResponseInterface;
use Swag\Braintree\Framework\Serializer\EntityNormalizer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ResponseSerializerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => 'serializeResponse'];
    }

    public function serializeResponse(ViewEvent $event): void
    {
        $result = $event->getControllerResult();

        if ($result instanceof ResponseInterface) {
            return;
        }

        $serialized = $this->serializer->serialize(
            $result,
            'json',
            [
                EntityNormalizer::ORIGINAL_DATA => \is_array($result) ? current($result) : $result,
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['__initializer__', '__cloner__', '__isInitialized__'],
            ]
        );

        $response = new Response($serialized, Response::HTTP_OK);
        $response->headers->add(['Content-Type' => 'application/json']);

        $event->setResponse($response);
    }
}
