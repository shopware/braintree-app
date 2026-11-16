import { test as base } from '@playwright/test';

export interface BraintreeCard {
    number: string
    /** MM/YYYY */
    expirationDate: string
    cvv: string
}

export type FillBraintreeCardTask = Task<[card: BraintreeCard]>;

/**
 * Cardholder name and postal code are prefilled from the logged-in customer.
 *
 * Uses plain fill() instead of ShopCustomer.fillsIn(): its accessibility checks scroll the input into view,
 * which waits for the element to be stable and never settles inside the cross-origin Hosted Fields iframes.
 */
export const FillBraintreeCard = base.extend<FixtureTypes>({
    FillBraintreeCard: async ({ StorefrontCheckoutConfirm }, use) => {
        const task: FillBraintreeCardTask = (card) => {
            return async function FillBraintreeCard() {
                await StorefrontCheckoutConfirm.braintreeCardNumberInput.fill(card.number);
                await StorefrontCheckoutConfirm.braintreeExpirationDateInput.fill(card.expirationDate);
                await StorefrontCheckoutConfirm.braintreeCvvInput.fill(card.cvv);
            };
        };

        await use(task);
    },
});
