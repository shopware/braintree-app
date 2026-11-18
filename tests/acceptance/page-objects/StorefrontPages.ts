import { test as base } from '@playwright/test';
import { BraintreeCardPayment } from './Storefront/Checkout/BraintreeCardPayment';
import { CheckoutConfirm } from './Storefront/Checkout/CheckoutConfirm';
import { CheckoutOrderEdit } from './Storefront/Checkout/CheckoutOrderEdit';

export interface StorefrontPageTypes {
    StorefrontBraintreeCardPayment: BraintreeCardPayment
    StorefrontCheckoutConfirm: CheckoutConfirm
    StorefrontCheckoutOrderEdit: CheckoutOrderEdit
}

export const test = base.extend<FixtureTypes>({
    StorefrontBraintreeCardPayment: async ({ StorefrontPage }, use) => {
        await use(new BraintreeCardPayment(StorefrontPage));
    },

    StorefrontCheckoutConfirm: async ({ StorefrontPage }, use) => {
        await use(new CheckoutConfirm(StorefrontPage));
    },

    StorefrontCheckoutOrderEdit: async ({ StorefrontPage }, use) => {
        await use(new CheckoutOrderEdit(StorefrontPage));
    },
});
