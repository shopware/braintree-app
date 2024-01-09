<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\Exception;

use Symfony\Component\HttpFoundation\Response;

class ShopNotFoundException extends BraintreeHttpException
{
    public const ERROR_CODE = 'SWAG_BRAINTREE__SHOP_NOT_FOUND';

    public function __construct(string $shopId, \Throwable $e = null)
    {
        parent::__construct(
            'Shop "{{ shopId }}" not found.',
            ['shopId' => $shopId],
            $e
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
