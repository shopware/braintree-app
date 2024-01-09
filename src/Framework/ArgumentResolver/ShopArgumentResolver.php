<?php declare(strict_types=1);

namespace Swag\Braintree\Framework\ArgumentResolver;

use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Framework\Request\ShopResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ShopArgumentResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly ShopResolver $shopResolver,
    ) {
    }

    /**
     * @return iterable<ShopInterface>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$argument->getType() || !\is_a($argument->getType(), ShopInterface::class, true)) {
            return [];
        }

        yield $this->shopResolver->resolveShop($request);
    }
}
