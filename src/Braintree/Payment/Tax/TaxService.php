<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Payment\Tax;

use Shopware\App\SDK\Context\Cart\CalculatedTax;
use Shopware\App\SDK\Framework\Collection;
use Swag\Braintree\Braintree\Util\FloatComparator;

class TaxService
{
    /**
     * @param Collection<CalculatedTax> $taxes
     */
    public function sumTaxes(Collection $taxes): float
    {
        $amounts = $taxes->map(fn (CalculatedTax $calculatedTax) => $calculatedTax->getTax());

        return FloatComparator::cast(\array_sum($amounts));
    }
}
