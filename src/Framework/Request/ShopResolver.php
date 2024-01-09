<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\Request;

use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\App\SDK\Shop\ShopRepositoryInterface;
use Shopware\App\SDK\Shop\ShopResolver as ShopwareShopResolver;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class ShopResolver
{
    public const SHOP_ID = 'shop-id';

    /**
     * @param ShopRepositoryInterface<ShopInterface> $shopRepository
     */
    public function __construct(
        private readonly ShopwareShopResolver $shopwareShopResolver,
        private readonly ShopRepositoryInterface $shopRepository,
        private readonly HttpMessageFactoryInterface $httpMessageFactory,
    ) {
    }

    public function resolveShop(Request $request): ShopInterface
    {
        $shop = $this->resolveShopFromSession($request);

        if (!$shop) {
            $shop = $this->shopwareShopResolver->resolveShop($this->httpMessageFactory->createRequest($request));
        }

        return $shop;
    }

    private function resolveShopFromSession(Request $request): ?ShopInterface
    {
        if (!$request->hasSession()) {
            return null;
        }

        if (!$request->getSession()->has(self::SHOP_ID)) {
            return null;
        }

        $shopId = $request->getSession()->get(self::SHOP_ID);

        return $this->shopRepository->getShopFromId($shopId);
    }
}
