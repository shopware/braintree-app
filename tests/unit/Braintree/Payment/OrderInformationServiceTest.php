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
            'commodityCode' => '1234567890AB',
            'description' => \str_repeat('F', 127),
            'discountAmount' => 0.0,
            'productCode' => 'product-10',
            'taxAmount' => 2.0,
            'unitOfMeasure' => 'unit',
        ], [
            'kind' => 'debit',
            'name' => 'Product -10€',
            'quantity' => 1,
            'totalAmount' => -10,
            'unitAmount' => -10,
            'unitTaxAmount' => 0,
            'commodityCode' => '1234567890',
            'description' => 'Product that costs -10€',
            'discountAmount' => 0.0,
            'productCode' => 'product--10',
            'taxAmount' => 0.0,
            'unitOfMeasure' => 'unit',
        ], [
            'kind' => 'debit',
            'name' => 'Product 220€',
            'quantity' => 1,
            'totalAmount' => 220,
            'unitAmount' => 220,
            'unitTaxAmount' => 22,
            'commodityCode' => null,
            'description' => null,
            'discountAmount' => 0.0,
            'productCode' => 'product-220',
            'taxAmount' => 22.0,
            'unitOfMeasure' => 'unit',
        ], [
            'kind' => 'debit',
            'name' => 'Product 4.456€',
            'quantity' => 1,
            'totalAmount' => 4.46,
            'unitAmount' => 4.46,
            'unitTaxAmount' => 0.45,
            'commodityCode' => null,
            'description' => null,
            'discountAmount' => 0.0,
            'productCode' => 'product-4456',
            'taxAmount' => 0.45,
            'unitOfMeasure' => 'unit',
        ]];

        $customer = $this->orderInformationService->extractLineItems($this->paymentPayAction);

        static::assertEquals($excepted, $customer);
    }

    public function testExtractDiscountLineItem(): void
    {
        $ids = new IdsCollection();

        $action = new PaymentPayAction(
            $this->shop,
            new ActionSource('this-is-url', 'this-is-app-version'),
            $this->createOrderWithDiscount($ids),
            $this->createOrderTransaction($ids),
            null,
            null,
            [],
        );

        $lineItems = $this->orderInformationService->extractLineItems($action);

        static::assertCount(1, $lineItems);

        $expected = [
            'kind' => 'debit',
            'name' => 'Discount 20-4567€',
            'quantity' => 2,
            'totalAmount' => -40.91,
            'unitAmount' => -20.46,
            'unitTaxAmount' => 5.0,
            'commodityCode' => '1234567890',
            'description' => null,
            'discountAmount' => -40.91,
            'productCode' => 'discount-20-',
            'taxAmount' => 10.0,
            'unitOfMeasure' => 'unit',
        ];

        static::assertEquals($expected, $lineItems[0]);
    }

    public function testExtractLineItemsWithMoreThan249(): void
    {
        $lineItemData = [
            'label' => 'Aerodynamic Bronze Loungerie',
            'good' => true,
            'quantity' => 1,
            'description' => 'foo',
            'type' => 'product',
            'referencedId' => 'product-id',
            'payload' => [
                'customFields' => [
                    'commodityCode' => '123456789',
                ],
            ],
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
            'commodityCode' => null,
            'description' => 'foo',
            'discountAmount' => 0,
            'productCode' => 'product-id',
            'taxAmount' => 19.0,
            'unitOfMeasure' => 'unit',
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

    public function testExtractShippingTaxAmount(): void
    {
        $shippingTaxAmount = $this->orderInformationService->extractShippingTaxAmount($this->paymentPayAction);
        static::assertSame(1.46, $shippingTaxAmount);
    }
}
