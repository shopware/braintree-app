<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Contract;

use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Tests\IdsCollection;

trait PaymentPayActionHelperTrait
{
    use OrderHelperTrait;
    use OrderTransactionHelperTrait;

    /**
     * @param array<mixed> $requestData
     */
    private function createPaymentPayAction(IdsCollection $orderIds, ShopInterface $shop, array $requestData = []): PaymentPayAction
    {
        $actionSource = new ActionSource('this-is-url', 'this-is-app-version');

        return new PaymentPayAction(
            $shop,
            $actionSource,
            $this->createOrder($orderIds),
            $this->createOrderTransaction($orderIds),
            null,
            null,
            $requestData,
        );
    }
}
