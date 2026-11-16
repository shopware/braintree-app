import type { Page, Locator } from '@shopware-ag/acceptance-test-suite';
import { StorefrontPageObjects } from '@shopware-ag/acceptance-test-suite';

export class CheckoutConfirm extends StorefrontPageObjects.CheckoutConfirm {
    public readonly paymentBraintreeName = 'Credit or Debit Card (by Braintree)';
    public readonly paymentBraintree: Locator;
    /** Shopware's notice when it switched away from Braintree because the checkout gateway removed it */
    public readonly paymentBraintreeUnavailableAlert: Locator;

    constructor(page: Page) {
        super(page);

        this.paymentBraintree = this.paymentMethodRadioGroup.getByRole('radio', { name: this.paymentBraintreeName });
        this.paymentBraintreeUnavailableAlert = page.getByRole('alert').filter({ hasText: `"${this.paymentBraintreeName}" payment is not available` });
    }
}
