<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Braintree\Payment\Tax;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedTax;
use Shopware\App\SDK\Framework\Collection;
use Swag\Braintree\Braintree\Payment\Tax\TaxService;

#[CoversClass(TaxService::class)]
class TaxServiceTest extends TestCase
{
    public function testSumTaxes(): void
    {
        $tax1 = new CalculatedTax([
            'taxRate' => 10.0,
            'price' => 100.0,
            'tax' => 10.0,
        ]);

        $tax2 = new CalculatedTax([
            'taxRate' => 20.0,
            'price' => 100.0,
            'tax' => 20.0,
        ]);

        $taxService = new TaxService();

        $sum = $taxService->sumTaxes(new Collection([$tax1, $tax2]));

        static::assertSame(30.0, $sum);
    }
}
