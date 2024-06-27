<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Braintree\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Cart\LineItem;
use Shopware\App\SDK\Context\Order\Order;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Framework\Collection;
use Swag\Braintree\Braintree\Payment\OrderInformationService;
use Swag\Braintree\Braintree\Payment\Tax\TaxService;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Tests\Contract\OrderHelperTrait;
use Swag\Braintree\Tests\Contract\OrderTransactionHelperTrait;
use Swag\Braintree\Tests\Contract\PaymentPayActionHelperTrait;
use Swag\Braintree\Tests\IdsCollection;

#[CoversClass(OrderInformationService::class)]
#[CoversClass(PaymentPayActionHelperTrait::class)]
#[CoversClass(OrderHelperTrait::class)]
#[CoversClass(OrderTransactionHelperTrait::class)]
class OrderInformationServiceTest extends TestCase
{
    use PaymentPayActionHelperTrait;

    private IdsCollection $orderIds;

    private OrderInformationService $orderInformationService;

    private ShopEntity $shop;

    private PaymentPayAction $paymentPayAction;

    protected function setUp(): void
    {
        $this->orderIds = new IdsCollection();
        $this->orderInformationService = new OrderInformationService(new TaxService());
        $this->shop = new ShopEntity('this-is-shop-id', '', 'this-is-shop-secret');

        $this->paymentPayAction = $this->createPaymentPayAction($this->orderIds, $this->shop);
    }

    public function testExtractTaxAmount(): void
    {
        $taxAmount = $this->orderInformationService->extractTaxAmount($this->paymentPayAction);
        static::assertEquals(20.46, $taxAmount);
    }

    public function testExtractShippingAddress(): void
    {
        $expected = [
            'id' => $this->orderIds->get('order-shipping-address-id'),
            'address' => [
                'company' => \str_repeat('company', 36) . 'com',
                'countryCodeAlpha3' => 'HUN',
                'extendedAddress' => \str_repeat('additionalAddressLine1', 11) . 'additionalAdd',
                'firstName' => \str_repeat('Max', 85),
                'lastName' => \str_repeat('Mustermann', 25) . 'Muste',
                'locality' => \str_repeat('Schöppingen', 21) . 'Sch',
                'postalCode' => '123456789',
                'region' => \str_repeat('countryState', 21) . 'cou',
                'streetAddress' => \str_repeat('Ebbinghoff 10', 19) . 'Ebbingho',
            ],
        ];

        $shippingAddress = $this->orderInformationService->extractShippingAddress($this->paymentPayAction);
        static::assertEquals($expected, $shippingAddress);
    }

    public function testExtractBillingAddress(): void
    {
        $expected = [
            'id' => $this->orderIds->get('order-billing-address-id'),
            'address' => [
                'company' => null,
                'countryCodeAlpha3' => 'HTI',
                'extendedAddress' => null,
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'locality' => 'Berlin',
                'postalCode' => '10332',
                'region' => null,
                'streetAddress' => 'Bahnhofstraße 27',
            ],
        ];

        $billingAddress = $this->orderInformationService->extractBillingAddress($this->paymentPayAction);
        static::assertEquals($expected, $billingAddress);
    }

    public function testExtractCustomer(): void
    {
        $excepted = [
            'id' => $this->orderIds->get('order-order-customer-id'),
            'company' => \str_repeat('company', 36) . 'com',
            'email' => \str_repeat('test@example.com', 15) . 'test@example.co',
            'firstName' => \str_repeat('Max', 85),
            'lastName' => \str_repeat('Mustermann', 25) . 'Muste',
        ];

        $customer = $this->orderInformationService->extractCustomer($this->paymentPayAction);

        static::assertEquals($excepted, $customer);
    }

    public function testExtractLineItems(): void
    {
        $excepted = [[
            'kind' => 'debit',
            'name' => 'Product 10€Product 10€Product 1',
            'quantity' => 1,
            'totalAmount' => 10,
            'unitAmount' => 10,
            'unitTaxAmount' => 2,
        ], [
            'kind' => 'debit',
            'name' => 'Product -10€',
            'quantity' => 1,
            'totalAmount' => -10,
            'unitAmount' => -10,
            'unitTaxAmount' => 0,
        ], [
            'kind' => 'debit',
            'name' => 'Product 220€',
            'quantity' => 1,
            'totalAmount' => 220,
            'unitAmount' => 220,
            'unitTaxAmount' => 22,
        ], [
            'kind' => 'debit',
            'name' => 'Product 4.456€',
            'quantity' => 1,
            'totalAmount' => 4.46,
            'unitAmount' => 4.46,
            'unitTaxAmount' => 0.45,
        ]];

        $customer = $this->orderInformationService->extractLineItems($this->paymentPayAction);

        static::assertEquals($excepted, $customer);
    }

    public function testExtractLineItemsWithMoreThan249(): void
    {
        $lineItemData = [
            'label' => 'Aerodynamic Bronze Loungerie',
            'good' => true,
            'quantity' => 1,
            'price' => [
                'totalPrice' => 100,
                'unitPrice' => 100,
                'calculatedTaxes' => [[
                    'taxRate' => 19,
                    'tax' => 19,
                ]],
            ],
        ];

        $expected = [
            'quantity' => 1,
            'kind' => 'debit',
            'name' => 'Aerodynamic Bronze Loungerie',
            'totalAmount' => 100.0,
            'unitAmount' => 100.0,
            'unitTaxAmount' => 19.0,
        ];



        $order = $this->createMock(Order::class);
        $order
            ->expects(static::once())
            ->method('getLineItems')
            ->willReturn(new Collection(array_fill(0, 250, new LineItem($lineItemData))));

        $paymentPayAction = new PaymentPayAction(
            $this->shop,
            $this->createMock(ActionSource::class),
            $order,
            $this->createOrderTransaction($this->orderIds),
            null,
        );

        $lineItems = $this->orderInformationService->extractLineItems($paymentPayAction);

        static::assertCount(249, $lineItems);
        static::assertEquals(array_fill(0, 249, $expected), $lineItems);
    }

    public function testExtractDiscountAmount(): void
    {
        $discountAmount = $this->orderInformationService->extractDiscountAmount($this->paymentPayAction);
        static::assertEquals(30, $discountAmount);
    }

    public function testExtractCurrencyId(): void
    {
        $currencyId = $this->orderInformationService->extractCurrencyId($this->paymentPayAction);
        static::assertEquals($this->orderIds->get('order-currency-id'), $currencyId);
    }

    public function testExtractSalesChannelId(): void
    {
        $salesChannelId = $this->orderInformationService->extractSalesChannelId($this->paymentPayAction);
        static::assertEquals($this->orderIds->get('order-sales-channel-id'), $salesChannelId);
    }
}
