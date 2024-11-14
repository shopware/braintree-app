<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Contract;

use Shopware\App\SDK\Context\Order\Order;
use Swag\Braintree\Braintree\Payment\OrderInformationService;
use Swag\Braintree\Tests\IdsCollection;

/**
 * @infection-ignore-all - besides $ids, this is static data
 */
trait OrderHelperTrait
{
    private function createOrder(IdsCollection $ids): Order
    {
        return new Order([
            'orderNumber' => '10068',
            'salesChannelId' => $ids->get('order-sales-channel-id'),
            'price' => [
                'netPrice' => 180,
                'totalPrice' => 200,
                'calculatedTaxes' => [['tax' => 20, 'taxRate' => 10, 'price' => 200]],
                'taxRules' => [['taxRate' => 10, 'percentage' => 100]],
                'positionPrice' => 200,
                'taxStatus' => 'gross',
                'rawTotal' => 200,
            ],
            'amountTotal' => 200,
            'amountNet' => 180,
            'positionPrice' => 200,
            'taxStatus' => 'gross',
            'shippingTotal' => 5,
            'shippingCosts' => [
                'unitPrice' => 5,
                'quantity' => 1,
                'totalPrice' => 5,
                'calculatedTaxes' => [['tax' => 0, 'taxRate' => 5, 'price' => 0]],
                'taxRules' => [['taxRate' => 5, 'percentage' => 100]],
            ],
            'orderCustomer' => [
                'email' => \str_repeat('test@example.com', 16),
                'orderId' => $ids->get('order-id'),
                'firstName' => \str_repeat('Max', 86),
                'lastName' => \str_repeat('Mustermann', 27),
                'title' => null,
                'company' => \str_repeat('company', 37),
                'customerNumber' => '1337',
                'customerId' => $ids->get('order-customer-id'),
                'id' => $ids->get('order-order-customer-id'),
            ],
            'currency' => [
                'isoCode' => 'EUR',
                'symbol' => '€',
                'shortName' => 'EUR',
                'name' => 'Euro',
                'itemRounding' => ['decimals' => 3, 'interval' => 0.001, 'roundForNet' => true],
                'totalRounding' => ['decimals' => 3, 'interval' => 0.001, 'roundForNet' => true],
                'id' => $ids->get('order-currency-id'),
            ],
            'billingAddress' => [
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'street' => 'Bahnhofstraße 27',
                'zipcode' => '10332',
                'city' => 'Berlin',
                'company' => null,
                'title' => null,
                'additionalAddressLine1' => null,
                'additionalAddressLine2' => null,
                'country' => [
                    'name' => 'Haiti',
                    'iso' => 'HT',
                    'iso3' => 'HTI',
                    'id' => $ids->get('order-country-id'),
                ],
                'id' => $ids->get('order-billing-address-id'),
            ],
            'deliveries' => [[
                'shippingCosts' => [
                    'unitPrice' => 5,
                    'quantity' => 1,
                    'totalPrice' => 5,
                    'calculatedTaxes' => [[
                        'tax' => 1.4567,
                        'taxRate' => 5,
                        'price' => 1.4567,
                    ]],
                ],
                'shippingOrderAddress' => [
                    'firstName' => \str_repeat('Max', 86),
                    'lastName' => \str_repeat('Mustermann', 26),
                    'street' => \str_repeat('Ebbinghoff 10', 20),
                    'zipcode' => '1234567890',
                    'city' => \str_repeat('Schöppingen', 22),
                    'company' => \str_repeat('company', 37),
                    'title' => null,
                    'additionalAddressLine1' => \str_repeat('additionalAddressLine1', 12),
                    'additionalAddressLine2' => null,
                    'country' => [
                        'name' => 'Hungary',
                        'iso' => 'HU',
                        'iso3' => 'HUN',
                        'id' => $ids->get('order-country-id'),
                    ],
                    'countryState' => [
                        'name' => \str_repeat('countryState', 22),
                    ],
                    'id' => $ids->get('order-shipping-address-id'),
                ],
                'id' => $ids->get('order-delivery-id'),
            ]],
            'lineItems' => [[
                'quantity' => 1,
                'unitPrice' => 20,
                'totalPrice' => 20,
                'label' => 'Discount -20€',
                'description' => null,
                'good' => false,
                'type' => 'discount',
                'referencedId' => 'discount-20',
                'payload' => [
                    'customFields' => [
                        OrderInformationService::LINE_ITEM_COMMODITY_CODE_CUSTOM_FIELD => '1234567890',
                    ],
                ],
                'price' => [
                    'unitPrice' => -20,
                    'quantity' => 1,
                    'totalPrice' => -20,
                    'calculatedTaxes' => [['tax' => 0, 'taxRate' => 0, 'price' => 0]],
                    'taxRules' => [['taxRate' => 0, 'percentage' => 100]],
                ],
                'id' => $ids->get('order-line-item-id'),
            ], [
                'quantity' => 1,
                'unitPrice' => 10,
                'totalPrice' => 10,
                'label' => \str_repeat('Product 10€', 3),
                'description' => \str_repeat('F', 128), // too long of a description for Braintree
                'good' => true,
                'type' => 'product',
                'referencedId' => 'product-10',
                'payload' => [
                    'customFields' => [
                        OrderInformationService::LINE_ITEM_COMMODITY_CODE_CUSTOM_FIELD => '1234567890ABCDEFG', // too long of a commodity code for Braintree
                    ],
                ],
                'price' => [
                    'unitPrice' => 10,
                    'quantity' => 1,
                    'totalPrice' => 10,
                    'calculatedTaxes' => [['tax' => 2, 'taxRate' => 10, 'price' => 10]],
                    'taxRules' => [['taxRate' => 10, 'percentage' => 100]],
                ],
                'id' => $ids->get('order-line-item-id'),
            ], [
                'quantity' => 1,
                'unitPrice' => 10,
                'totalPrice' => 10,
                'label' => 'Discount -10€',
                'description' => 'Discount over 10€',
                'good' => false,
                'type' => 'discount',
                'referencedId' => 'discount-10',
                'payload' => [
                    'customFields' => [
                        OrderInformationService::LINE_ITEM_COMMODITY_CODE_CUSTOM_FIELD => '1234567890',
                    ],
                ],
                'price' => [
                    'unitPrice' => -10,
                    'quantity' => 1,
                    'totalPrice' => -10,
                    'calculatedTaxes' => [['tax' => 0, 'taxRate' => 0, 'price' => 0]],
                    'taxRules' => [['taxRate' => 0, 'percentage' => 100]],
                ],
                'id' => $ids->get('order-line-item-id'),
            ], [
                'quantity' => 1,
                'unitPrice' => 10,
                'totalPrice' => 10,
                'label' => 'Product -10€',
                'description' => 'Product that costs -10€',
                'good' => true,
                'type' => 'product',
                'referencedId' => 'product--10',
                'payload' => [
                    'customFields' => [
                        OrderInformationService::LINE_ITEM_COMMODITY_CODE_CUSTOM_FIELD => '1234567890',
                    ],
                ],
                'price' => [
                    'unitPrice' => -10,
                    'quantity' => 1,
                    'totalPrice' => -10,
                    'calculatedTaxes' => [['tax' => 0, 'taxRate' => 0, 'price' => 0]],
                    'taxRules' => [['taxRate' => 0, 'percentage' => 100]],
                ],
                'id' => $ids->get('order-line-item-id'),
            ], [
                'quantity' => 1,
                'unitPrice' => 0,
                'totalPrice' => 0,
                'label' => 'Discount 0€',
                'description' => null,
                'good' => false,
                'type' => 'discount',
                'referencedId' => 'discount-0',
                'payload' => [
                    'customFields' => [
                        OrderInformationService::LINE_ITEM_COMMODITY_CODE_CUSTOM_FIELD => '1234567890',
                    ],
                ],
                'price' => [
                    'unitPrice' => 0,
                    'quantity' => 1,
                    'totalPrice' => 0,
                    'calculatedTaxes' => [['tax' => 0, 'taxRate' => 0, 'price' => 0]],
                    'taxRules' => [['taxRate' => 0, 'percentage' => 100]],
                ],
                'id' => $ids->get('order-line-item-id'),
            ], [
                'quantity' => 1,
                'unitPrice' => 220,
                'totalPrice' => 220,
                'label' => 'Product 220€',
                'description' => null,
                'good' => true,
                'type' => 'product',
                'referencedId' => 'product-220',
                'payload' => [
                    'customFields' => [
                        OrderInformationService::LINE_ITEM_COMMODITY_CODE_CUSTOM_FIELD => '',
                    ],
                ],
                'price' => [
                    'unitPrice' => 220,
                    'quantity' => 1,
                    'totalPrice' => 220,
                    'calculatedTaxes' => [['tax' => 22, 'taxRate' => 10, 'price' => 220]],
                    'taxRules' => [['taxRate' => 10, 'percentage' => 100]],
                ],
                'id' => $ids->get('order-line-item-id'),
            ], [
                'quantity' => 1,
                'unitPrice' => 4.456,
                'totalPrice' => 4.456,
                'label' => 'Product 4.456€',
                'description' => null,
                'good' => true,
                'type' => 'product',
                'referencedId' => 'product-4456',
                'payload' => [
                    'customFields' => [
                        OrderInformationService::LINE_ITEM_COMMODITY_CODE_CUSTOM_FIELD => null,
                    ],
                ],
                'price' => [
                    'unitPrice' => 4.456,
                    'quantity' => 1,
                    'totalPrice' => 4.456,
                    'calculatedTaxes' => [['tax' => 0.4456, 'taxRate' => 10, 'price' => 225]],
                    'taxRules' => [['taxRate' => 10, 'percentage' => 100]],
                ],
                'id' => $ids->get('order-line-item-id'),
            ]],
            'transactions' => [[
                'amount' => [
                    'unitPrice' => 200,
                    'quantity' => 1,
                    'totalPrice' => 200,
                    'calculatedTaxes' => [['tax' => 20, 'taxRate' => 10, 'price' => 200]],
                    'taxRules' => [['taxRate' => 10, 'percentage' => 100]],
                ],
                'id' => $ids->get('order-transaction-id'),
            ]],
            'itemRounding' => ['decimals' => 3, 'interval' => 0.001, 'roundForNet' => true],
            'totalRounding' => ['decimals' => 3, 'interval' => 0.001, 'roundForNet' => true],
            'id' => $ids->get('order-id'),
        ]);
    }

    private function createOrderWithDiscount(IdsCollection $ids): Order
    {
        return new Order([
            'orderNumber' => '10068',
            'salesChannelId' => $ids->get('order-sales-channel-id'),
            'price' => [
                'netPrice' => 180,
                'totalPrice' => 200,
                'calculatedTaxes' => [['tax' => 20, 'taxRate' => 10, 'price' => 200]],
                'taxRules' => [['taxRate' => 10, 'percentage' => 100]],
                'positionPrice' => 200,
                'taxStatus' => 'gross',
                'rawTotal' => 200,
            ],
            'amountTotal' => 200,
            'amountNet' => 180,
            'positionPrice' => 200,
            'taxStatus' => 'gross',
            'shippingTotal' => 5,
            'shippingCosts' => [
                'unitPrice' => 5,
                'quantity' => 1,
                'totalPrice' => 5,
                'calculatedTaxes' => [['tax' => 0, 'taxRate' => 5, 'price' => 0]],
                'taxRules' => [['taxRate' => 5, 'percentage' => 100]],
            ],
            'orderCustomer' => [
                'email' => \str_repeat('test@example.com', 16),
                'orderId' => $ids->get('order-id'),
                'firstName' => \str_repeat('Max', 86),
                'lastName' => \str_repeat('Mustermann', 27),
                'title' => null,
                'company' => \str_repeat('company', 37),
                'customerNumber' => '1337',
                'customerId' => $ids->get('order-customer-id'),
                'id' => $ids->get('order-order-customer-id'),
            ],
            'currency' => [
                'isoCode' => 'EUR',
                'symbol' => '€',
                'shortName' => 'EUR',
                'name' => 'Euro',
                'itemRounding' => ['decimals' => 3, 'interval' => 0.001, 'roundForNet' => true],
                'totalRounding' => ['decimals' => 3, 'interval' => 0.001, 'roundForNet' => true],
                'id' => $ids->get('order-currency-id'),
            ],
            'billingAddress' => [
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'street' => 'Bahnhofstraße 27',
                'zipcode' => '10332',
                'city' => 'Berlin',
                'company' => null,
                'title' => null,
                'additionalAddressLine1' => null,
                'additionalAddressLine2' => null,
                'country' => [
                    'name' => 'Haiti',
                    'iso' => 'HT',
                    'iso3' => 'HTI',
                    'id' => $ids->get('order-country-id'),
                ],
                'id' => $ids->get('order-billing-address-id'),
            ],
            'deliveries' => [[
                'shippingCosts' => [
                    'unitPrice' => 5,
                    'quantity' => 1,
                    'totalPrice' => 5,
                    'calculatedTaxes' => [[
                        'tax' => 0,
                        'taxRate' => 5,
                        'price' => 0,
                    ]],
                ],
                'shippingOrderAddress' => [
                    'firstName' => \str_repeat('Max', 86),
                    'lastName' => \str_repeat('Mustermann', 26),
                    'street' => \str_repeat('Ebbinghoff 10', 20),
                    'zipcode' => '1234567890',
                    'city' => \str_repeat('Schöppingen', 22),
                    'company' => \str_repeat('company', 37),
                    'title' => null,
                    'additionalAddressLine1' => \str_repeat('additionalAddressLine1', 12),
                    'additionalAddressLine2' => null,
                    'country' => [
                        'name' => 'Hungary',
                        'iso' => 'HU',
                        'iso3' => 'HUN',
                        'id' => $ids->get('order-country-id'),
                    ],
                    'countryState' => [
                        'name' => \str_repeat('countryState', 22),
                    ],
                    'id' => $ids->get('order-shipping-address-id'),
                ],
                'id' => $ids->get('order-delivery-id'),
            ]],
            'lineItems' => [[
                'quantity' => 2,
                'unitPrice' => 20.4567,
                'totalPrice' => 40.9134,
                'label' => 'Discount 20-4567€',
                'description' => null,
                'good' => true,
                'type' => 'discount',
                'referencedId' => 'discount-20-4567',
                'payload' => [
                    'customFields' => [
                        OrderInformationService::LINE_ITEM_COMMODITY_CODE_CUSTOM_FIELD => '1234567890',
                    ],
                ],
                'price' => [
                    'unitPrice' => -20.4567,
                    'quantity' => 2,
                    'totalPrice' => -40.9134,
                    'calculatedTaxes' => [['tax' => 10, 'taxRate' => 10, 'price' => -20.4567]],
                    'taxRules' => [['taxRate' => 0, 'percentage' => 100]],
                ],
                'id' => $ids->get('order-line-item-id'),
            ]],
            'transactions' => [[
                'amount' => [
                    'unitPrice' => -20.4567,
                    'quantity' => 2,
                    'totalPrice' => -40.9134,
                    'calculatedTaxes' => [['tax' => 10, 'taxRate' => 10, 'price' => -20.4567]],
                    'taxRules' => [['taxRate' => 0, 'percentage' => 100]],
                ],
                'id' => $ids->get('order-discount-transaction-id'),
            ]],
            'itemRounding' => ['decimals' => 3, 'interval' => 0.001, 'roundForNet' => true],
            'totalRounding' => ['decimals' => 3, 'interval' => 0.001, 'roundForNet' => true],
            'id' => $ids->get('order-discount-id'),
        ]);
    }
}
