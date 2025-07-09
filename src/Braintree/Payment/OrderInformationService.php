<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Payment;

use Shopware\App\SDK\Context\Cart\LineItem;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Context\SalesChannelContext\Address;
use Swag\Braintree\Braintree\Payment\Tax\TaxService;

class OrderInformationService
{
    public const LINE_ITEM_TYPE_DEBIT = 'debit';
    public const LINE_ITEM_TYPE_CREDIT = 'credit';
    public const LINE_ITEM_COMMODITY_CODE_CUSTOM_FIELD = 'swag_braintree_app_commodity_code';
    public const CUSTOM_FIELDS = 'swag_braintree_app_custom_fields';

    public function __construct(
        private readonly TaxService $taxService,
    ) {
    }

    public function extractTaxAmount(PaymentPayAction $payment): float
    {
        return \round($this->taxService->sumTaxes($payment->orderTransaction->getAmount()->getCalculatedTaxes()), 2);
    }

    public function extractShippingTaxAmount(PaymentPayAction $payment): float
    {
        return \round($this->taxService->sumTaxes($payment->order->getDeliveries()->first()->getShippingCosts()->getCalculatedTaxes()), 2);
    }

    /**
     * @return array{id: string, address: array<string, mixed>}
     */
    public function extractShippingAddress(PaymentPayAction $payment): array
    {
        $shippingAddress = $payment->order->getDeliveries()->first()->getShippingOrderAddress();

        return [
            'id' => $shippingAddress->getId(),
            'address' => $this->extractAddress($shippingAddress),
        ];
    }

    /**
     * @return array{id: string, address: array<string, mixed>}
     */
    public function extractBillingAddress(PaymentPayAction $payment): array
    {
        $billingAddress = $payment->order->getBillingAddress();

        return [
            'id' => $billingAddress->getId(),
            'address' => $this->extractAddress($billingAddress),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function extractCustomer(PaymentPayAction $payment): array
    {
        $customer = $payment->order->getOrderCustomer();

        return [
            'id' => $customer->getId(),
            'company' => $this->substr($customer->getCompany(), 255),
            'email' => $this->substr($customer->getEmail(), 255),
            'firstName' => $this->substr($customer->getFirstName(), 255),
            'lastName' => $this->substr($customer->getLastName(), 255),
        ];
    }

    /**
     * @return array<int, array<mixed>>
     */
    public function extractLineItems(PaymentPayAction $payment): array
    {
        $orderLineItems = $payment->order->getLineItems();
        $lineItems = [];

        // @infection-ignore-all - if 249 or 248, it really doesn't matter
        if (\count($orderLineItems) > 249) {
            $orderLineItems = \array_slice($orderLineItems->all(), 0, 249);
        }

        foreach ($orderLineItems as $lineItem) {
            if (!$lineItem->isGood()) {
                continue;
            }

            $lineItems[] = [
                'commodityCode' => $this->extractCommodityCode($lineItem),
                'description' => $this->substr($lineItem->getDescription(), 127),
                'discountAmount' => $lineItem->getType() === 'discount' ? \round($lineItem->getPrice()->getTotalPrice(), 2) : 0.0,
                'kind' => self::LINE_ITEM_TYPE_DEBIT,
                'name' => $this->substr($lineItem->getLabel(), 35),
                'productCode' => $this->substr($lineItem->getReferencedId(), 12),
                'quantity' => $lineItem->getQuantity(),
                'taxAmount' => \round($this->taxService->sumTaxes($lineItem->getPrice()->getCalculatedTaxes()), 2),
                'totalAmount' => \round($lineItem->getPrice()->getTotalPrice(), 2),
                'unitAmount' => \round($lineItem->getPrice()->getUnitPrice(), 2),
                'unitTaxAmount' => \round($this->taxService->sumTaxes($lineItem->getPrice()->getCalculatedTaxes()) / $lineItem->getQuantity(), 2),
                'unitOfMeasure' => 'unit',
            ];
        }

        return $lineItems;
    }

    public function extractDiscountAmount(PaymentPayAction $payment): string
    {
        $amount = 0;
        foreach ($payment->order->getLineItems() as $lineItem) {
            /** @infection-ignore-all - > or >= doesn't matter */
            if ($lineItem->isGood() || $lineItem->getPrice()->getTotalPrice() > 0) {
                continue;
            }

            $amount += $lineItem->getPrice()->getTotalPrice();
        }

        return (string) \abs($amount);
    }

    public function extractCurrencyId(PaymentPayAction $payment): string
    {
        return $payment->order->getCurrency()->getId();
    }

    public function extractSalesChannelId(PaymentPayAction $payment): string
    {
        return $payment->order->getSalesChannelId();
    }

    /**
     * @return array<string, string|int>
     */
    public function extractCustomFields(PaymentPayAction $payment): array
    {
        $json = $payment->order->getCustomFields()[self::CUSTOM_FIELDS] ?? '{}';

        if (!\is_string($json)) {
            return [];
        }

        $customFields = \json_decode($json, true);

        if (!\is_array($customFields)) {
            return [];
        }

        $processedFields = [];
        foreach ($customFields as $key => $value) {
            if (!\is_string($key) || !\is_string($value) && !\is_numeric($value)) {
                continue;
            }

            $key = $this->substr($key, 255);
            $processedFields[$key] = $value;
        }

        return $processedFields;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractAddress(Address $address): array
    {
        return [
            'company' => $this->substr($address->getCompany(), 255),
            'countryCodeAlpha3' => $address->getCountry()->getIso3(),
            'extendedAddress' => $this->substr($address->getAdditionalAddressLine1(), 255),
            'firstName' => $this->substr($address->getFirstName(), 255),
            'lastName' => $this->substr($address->getLastName(), 255),
            'locality' => $this->substr($address->getCity(), 255),
            'postalCode' => $this->substr($address->getZipCode(), 9),
            'region' => $this->substr($address->getCountryState()?->getName(), 255),
            'streetAddress' => $this->substr($address->getStreet(), 255),
        ];
    }

    private function extractCommodityCode(LineItem $lineItem): ?string
    {
        $payload = $lineItem->getPayload();

        if (!\array_key_exists('customFields', $payload)) {
            return null;
        }

        if (!\array_key_exists(self::LINE_ITEM_COMMODITY_CODE_CUSTOM_FIELD, $payload['customFields'])) {
            return null;
        }

        return $this->substr($payload['customFields'][self::LINE_ITEM_COMMODITY_CODE_CUSTOM_FIELD], 12);
    }

    private function substr(?string $value, int $length): ?string
    {
        return $value === null ? $value : \substr($value, 0, $length);
    }
}
