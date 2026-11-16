import type { Page, Locator } from '@shopware-ag/acceptance-test-suite';

/**
 * The app's card form and the 3D Secure challenge, which the storefront shows wherever the card payment is selected:
 * on the confirm page and on the edit order page after a failed payment.
 */
export class BraintreeCardPayment {
    private readonly hostedFields: Locator;
    public readonly cardNumberInput: Locator;
    public readonly expirationDateInput: Locator;
    public readonly cvvInput: Locator;

    /** The containers around the Hosted Fields iframes, which the app marks with `is-invalid` */
    public readonly cardNumberField: Locator;
    public readonly expirationDateField: Locator;
    public readonly cvvField: Locator;
    public readonly cardholderNameField: Locator;
    public readonly postalCodeField: Locator;

    public readonly threeDSecureChallengeCodeInput: Locator;
    public readonly threeDSecureChallengeSubmitButton: Locator;

    constructor(page: Page) {
        this.hostedFields = page.locator('.swag-braintree-hosted-fields');
        this.cardNumberInput = this.hostedFieldInput('number', '#credit-card-number');
        this.expirationDateInput = this.hostedFieldInput('expirationDate', '#expiration');
        this.cvvInput = this.hostedFieldInput('cvv', '#cvv');

        this.cardNumberField = page.locator('#sw-braintree-payment-method__number');
        this.expirationDateField = page.locator('#sw-braintree-payment-method__expirationDate');
        this.cvvField = page.locator('#sw-braintree-payment-method__cvv');
        this.cardholderNameField = page.locator('#sw-braintree-payment-method__cardholderName');
        this.postalCodeField = page.locator('#sw-braintree-payment-method__postalCode');

        // Cardinal renders the 3D Secure challenge of the sandbox issuer in a cross-origin iframe inside its modal
        const threeDSecureChallenge = page.locator('#Cardinal-CCA-IFrame').contentFrame();
        this.threeDSecureChallengeCodeInput = threeDSecureChallenge.getByPlaceholder('Enter Code Here');
        this.threeDSecureChallengeSubmitButton = threeDSecureChallenge.getByRole('button', { name: 'Submit' });
    }

    // Each Hosted Field is a cross-origin iframe served by Braintree
    private hostedFieldInput(key: string, inputSelector: string): Locator {
        return this.hostedFields
            .locator(`iframe[name="braintree-hosted-field-${key}"]`)
            .contentFrame()
            .locator(inputSelector);
    }
}
