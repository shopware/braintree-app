<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\Exception;

use Symfony\Component\HttpFoundation\Response;

class EntityPropertyRequiredException extends BraintreeHttpException
{
    public const ERROR_CODE = 'SWAG_BRAINTREE__ENTITY_PROPERTY_EXCEPTION';

    public function __construct(string $message, array $parameters = [])
    {
        parent::__construct(\sprintf('"%s" is required to be set', $message), $parameters, null);
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
