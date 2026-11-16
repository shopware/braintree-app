import type { Page, Locator } from '@shopware-ag/acceptance-test-suite';
import { StorefrontPageObjects } from '@shopware-ag/acceptance-test-suite';

export class CheckoutConfirm extends StorefrontPageObjects.CheckoutConfirm {
    public readonly paymentBraintreeName = 'Credit or Debit Card (by Braintree)';
    public readonly paymentBraintree: Locator;

    public readonly braintreeHostedFields: Locator;
    public readonly braintreeCardNumberInput: Locator;
    public readonly braintreeExpirationDateInput: Locator;
    public readonly braintreeCvvInput: Locator;
    public readonly braintreeCardholderNameInput: Locator;
    public readonly braintreePostalCodeInput: Locator;

    constructor(page: Page) {
        super(page);

        this.paymentBraintree = this.paymentMethodRadioGroup.getByRole('radio', { name: this.paymentBraintreeName });

        this.braintreeHostedFields = page.locator('.swag-braintree-hosted-fields');
        this.braintreeCardNumberInput = this.hostedFieldInput('number', '#credit-card-number');
        this.braintreeExpirationDateInput = this.hostedFieldInput('expirationDate', '#expiration');
        this.braintreeCvvInput = this.hostedFieldInput('cvv', '#cvv');
        this.braintreeCardholderNameInput = this.hostedFieldInput('cardholderName', '#cardholder-name');
        this.braintreePostalCodeInput = this.hostedFieldInput('postalCode', '#postal-code');
    }

    // Each Hosted Field is a cross-origin iframe served by Braintree
    private hostedFieldInput(key: string, inputSelector: string): Locator {
        return this.braintreeHostedFields
            .locator(`iframe[name="braintree-hosted-field-${key}"]`)
            .contentFrame()
            .locator(inputSelector);
    }
}
