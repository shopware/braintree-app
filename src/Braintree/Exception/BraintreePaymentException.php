<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Exception;

use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Framework\Exception\BraintreeHttpException;

class BraintreePaymentException extends BraintreeHttpException
{
    public const ERROR_CODE = 'SWAG_BRAINTREE__PAYMENT_EXCEPTION';

    public function __construct(string $message, array $parameters = [], \Throwable $e = null, ShopInterface $shop = null)
    {
        if ($shop) {
            $parameters['shopId'] = $shop->getShopId();
        }

        parent::__construct(\sprintf('Braintree payment process failed: %s', $message), $parameters, $e);
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
