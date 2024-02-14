<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Exception;

use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Framework\Exception\BraintreeHttpException;
use Symfony\Component\HttpFoundation\Response;

class BraintreeTransactionNotFoundException extends BraintreeHttpException
{
    public const ERROR_CODE = 'SWAG_BRAINTREE__TRANSACTION_NOT_FOUND';

    /**
     * @param string[] $orderTransactionIds
     */
    public function __construct(array $orderTransactionIds, ShopInterface $shop, ?string $braintreeTransactionId = null, array $parameters = [])
    {
        $parameters['shopId'] = $shop->getShopId();
        $parameters['orderTransactionIds'] = $orderTransactionIds;
        if ($braintreeTransactionId) {
            $parameters['braintreeTransactionId'] = $braintreeTransactionId;
        }

        parent::__construct('Braintree transaction not found', $parameters);
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
