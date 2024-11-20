<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Integration\Braintree\Payment;

use Swag\Braintree\Braintree\Payment\BraintreePaymentService;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Tests\Contract\PaymentPayActionHelperTrait;
use Swag\Braintree\Tests\IdsCollection;
use Swag\Braintree\Tests\Integration\ShopRegistrationTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BraintreePaymentServiceTest extends KernelTestCase
{
    use ShopRegistrationTrait;
    use PaymentPayActionHelperTrait;

    private IdsCollection $ids;

    protected function setUp(): void
    {
        $this->ids = new IdsCollection();
    }

    public function testValidTransaction(): void
    {
        $shop = new ShopEntity($this->ids->get('shop-id'), 'https://shop.com', 'devsecret');

        static::registerShop($this->ids->get('shop-id'));
        static::allowCurrency('EUR', $this->ids->get('shop-id'));

        $paymentPayAction = $this->createPaymentPayAction($this->ids, $shop);

        /** @var BraintreePaymentService $service */
        $service = $this->getContainer()->get(BraintreePaymentService::class);

        $response = $service->handleTransaction($paymentPayAction);

        dd($response);
    }
}