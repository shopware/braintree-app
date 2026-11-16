import type { Page, Locator } from '@shopware-ag/acceptance-test-suite';
import { StorefrontPageObjects } from '@shopware-ag/acceptance-test-suite';

export class CheckoutConfirm extends StorefrontPageObjects.CheckoutConfirm {
    public readonly paymentBraintreeName = 'Credit or Debit Card (by Braintree)';
    public readonly paymentBraintree: Locator;
    /** Shopware's notice when it switched away from Braintree because the checkout gateway removed it */
    public readonly paymentBraintreeUnavailableAlert: Locator;

    private readonly braintreeHostedFields: Locator;
    public readonly braintreeCardNumberInput: Locator;
    public readonly braintreeExpirationDateInput: Locator;
    public readonly braintreeCvvInput: Locator;

    /** The containers around the Hosted Fields iframes, which the app marks with `is-invalid` */
    public readonly braintreeCardNumberField: Locator;
    public readonly braintreeExpirationDateField: Locator;
    public readonly braintreeCvvField: Locator;
    public readonly braintreeCardholderNameField: Locator;
    public readonly braintreePostalCodeField: Locator;

    public readonly threeDSecureChallengeCodeInput: Locator;
    public readonly threeDSecureChallengeSubmitButton: Locator;

    constructor(page: Page) {
        super(page);

        this.paymentBraintree = this.paymentMethodRadioGroup.getByRole('radio', { name: this.paymentBraintreeName });
        this.paymentBraintreeUnavailableAlert = page.getByRole('alert').filter({ hasText: `"${this.paymentBraintreeName}" payment is not available` });

        this.braintreeHostedFields = page.locator('.swag-braintree-hosted-fields');
        this.braintreeCardNumberInput = this.hostedFieldInput('number', '#credit-card-number');
        this.braintreeExpirationDateInput = this.hostedFieldInput('expirationDate', '#expiration');
        this.braintreeCvvInput = this.hostedFieldInput('cvv', '#cvv');

        this.braintreeCardNumberField = page.locator('#sw-braintree-payment-method__number');
        this.braintreeExpirationDateField = page.locator('#sw-braintree-payment-method__expirationDate');
        this.braintreeCvvField = page.locator('#sw-braintree-payment-method__cvv');
        this.braintreeCardholderNameField = page.locator('#sw-braintree-payment-method__cardholderName');
        this.braintreePostalCodeField = page.locator('#sw-braintree-payment-method__postalCode');

        // Cardinal renders the 3D Secure challenge of the sandbox issuer in a cross-origin iframe inside its modal
        const threeDSecureChallenge = page.locator('#Cardinal-CCA-IFrame').contentFrame();
        this.threeDSecureChallengeCodeInput = threeDSecureChallenge.getByPlaceholder('Enter Code Here');
        this.threeDSecureChallengeSubmitButton = threeDSecureChallenge.getByRole('button', { name: 'Submit' });
    }

    // Each Hosted Field is a cross-origin iframe served by Braintree
    private hostedFieldInput(key: string, inputSelector: string): Locator {
        return this.braintreeHostedFields
            .locator(`iframe[name="braintree-hosted-field-${key}"]`)
            .contentFrame()
            .locator(inputSelector);
    }
}
