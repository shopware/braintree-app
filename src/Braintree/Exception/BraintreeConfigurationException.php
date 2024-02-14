<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Exception;

use Swag\Braintree\Framework\Exception\BraintreeHttpException;

class BraintreeConfigurationException extends BraintreeHttpException
{
    public const ERROR_CODE = 'SWAG_BRAINTREE__CONFIGURATION_EXCEPTION';

    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(
            'Braintree configuration is invalid.',
            [],
            $previous
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
