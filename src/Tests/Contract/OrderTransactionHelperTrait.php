<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Contract;

use Shopware\App\SDK\Context\Order\OrderTransaction;
use Swag\Braintree\Tests\Ids;

/**
 * @infection-ignore-all - this is static data
 */
trait OrderTransactionHelperTrait
{
    protected static function createOrderTransaction(): OrderTransaction
    {
        return new OrderTransaction([
            'amount' => [
                'unitPrice' => 119,
                'quantity' => 1,
                'totalPrice' => 119,
                'calculatedTaxes' => [['tax' => 19.078, 'taxRate' => 19.078, 'price' => 100]],
                'taxRules' => [['taxRate' => 19, 'percentage' => 100]],
            ],
            'id' => Ids::get('order-transaction-id'),
        ]);
    }
}
