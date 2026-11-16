import { test as base } from '@playwright/test';
import { CheckoutConfirm } from './Storefront/Checkout/CheckoutConfirm';

export interface StorefrontPageTypes {
    StorefrontCheckoutConfirm: CheckoutConfirm
}

export const test = base.extend<FixtureTypes>({
    StorefrontCheckoutConfirm: async ({ StorefrontPage }, use) => {
        await use(new CheckoutConfirm(StorefrontPage));
    },
});
