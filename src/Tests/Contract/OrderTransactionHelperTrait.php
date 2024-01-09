<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Contract;

use Shopware\App\SDK\Context\Order\OrderTransaction;
use Swag\Braintree\Tests\IdsCollection;

/**
 * @infection-ignore-all - besides $ids, this is static data
 */
trait OrderTransactionHelperTrait
{
    private function createOrderTransaction(IdsCollection $ids): OrderTransaction
    {
        return new OrderTransaction([
            'amount' => [
                'unitPrice' => 200,
                'quantity' => 1,
                'totalPrice' => 200,
                'calculatedTaxes' => [['tax' => 20.456, 'taxRate' => 10, 'price' => 200]],
                'taxRules' => [['taxRate' => 10, 'percentage' => 100]],
            ],
            'id' => $ids->get('order-transaction-id'),
        ]);
    }
}
