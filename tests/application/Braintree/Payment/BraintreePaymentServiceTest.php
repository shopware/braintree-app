<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Application\Braintree\Payment;

use Braintree\Transaction;
use Swag\Braintree\Braintree\Payment\BraintreePaymentService;
use Swag\Braintree\Braintree\Payment\ThreeDSecure;
use Swag\Braintree\Tests\Contract\PaymentPayActionHelperTrait;
use Swag\Braintree\Tests\Contract\ShopHelperTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BraintreePaymentServiceTest extends WebTestCase
{
    use PaymentPayActionHelperTrait;
    use ShopHelperTrait;

    public function testTransaction(): void
    {
        $shop = $this->registerShop();
        $action = $this->createPaymentPayActionForApplication(
            $shop,
            [BraintreePaymentService::BRAINTREE_NONCE => 'fake-three-d-secure-visa-full-authentication-nonce']
        );

        $service = static::getContainer()->get(BraintreePaymentService::class);
        $transaction = $service->handleTransaction($action);

        static::assertSame(Transaction::SUBMITTED_FOR_SETTLEMENT, $transaction->status);
        static::assertSame('119.00', $transaction->amount);
        static::assertSame('EUR', $transaction->currencyIsoCode);

        static::assertTrue($transaction->threeDSecureInfo->liabilityShifted);
        static::assertTrue($transaction->threeDSecureInfo->liabilityShiftPossible);
        static::assertSame(ThreeDSecure::STATUS_AUTHENTICATE_SUCCESSFUL, $transaction->threeDSecureInfo->status);
        static::assertSame(ThreeDSecure::ENROLLMENT_STATUS_YES, $transaction->threeDSecureInfo->enrolled);
    }
}
