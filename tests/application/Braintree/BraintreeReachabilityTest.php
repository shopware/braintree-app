<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Application\Braintree;

use Braintree\Gateway;
use Braintree\MerchantAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BraintreeReachabilityTest extends WebTestCase
{
    public function testBraintreeMerchantAPI(): void
    {
        $gateway = $this->getContainer()->get(Gateway::class);
        $merchant = $gateway->merchantAccount()->find($this->getContainer()->getParameter('BRAINTREE_TEST_MERCHANT_ACCOUNT_ID'));

        static::assertSame(MerchantAccount::STATUS_ACTIVE, $merchant->status);
        static::assertTrue($merchant->default);
    }
}
