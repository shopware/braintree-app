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
                'unitPrice' => 200,
                'quantity' => 1,
                'totalPrice' => 200,
                'calculatedTaxes' => [['tax' => 20.456, 'taxRate' => 10, 'price' => 200]],
                'taxRules' => [['taxRate' => 10, 'percentage' => 100]],
            ],
            'id' => Ids::get('order-transaction-id'),
        ]);
    }
}
