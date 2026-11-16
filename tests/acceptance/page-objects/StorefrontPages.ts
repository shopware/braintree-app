import { test as base } from '@playwright/test';
import { CheckoutConfirm } from './Storefront/Checkout/CheckoutConfirm';
import { CheckoutOrderEdit } from './Storefront/Checkout/CheckoutOrderEdit';

export interface StorefrontPageTypes {
    StorefrontCheckoutConfirm: CheckoutConfirm
    StorefrontCheckoutOrderEdit: CheckoutOrderEdit
}

export const test = base.extend<FixtureTypes>({
    StorefrontCheckoutConfirm: async ({ StorefrontPage }, use) => {
        await use(new CheckoutConfirm(StorefrontPage));
    },

    StorefrontCheckoutOrderEdit: async ({ StorefrontPage }, use) => {
        await use(new CheckoutOrderEdit(StorefrontPage));
    },
});
