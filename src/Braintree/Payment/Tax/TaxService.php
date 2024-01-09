<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Payment\Tax;

use Shopware\App\SDK\Context\Cart\CalculatedTax;
use Swag\Braintree\Braintree\Util\FloatComparator;

class TaxService
{
    /**
     * @param array<CalculatedTax> $taxes
     */
    public function sumTaxes(array $taxes): float
    {
        $amounts = \array_map(
            fn (CalculatedTax $calculatedTax) => $calculatedTax->getTax(),
            $taxes
        );

        return FloatComparator::cast(\array_sum($amounts));
    }
}
