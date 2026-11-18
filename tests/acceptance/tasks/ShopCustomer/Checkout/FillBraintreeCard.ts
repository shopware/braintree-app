import { test as base, expect } from '@playwright/test';

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
    FillBraintreeCard: async ({ StorefrontBraintreeCardPayment }, use) => {
        const task: FillBraintreeCardTask = (card) => {
            return async function FillBraintreeCard() {
                await StorefrontBraintreeCardPayment.cardNumberInput.fill(card.number);
                await StorefrontBraintreeCardPayment.expirationDateInput.fill(card.expirationDate);
                await StorefrontBraintreeCardPayment.cvvInput.fill(card.cvv);
                // the plugin handles a submit only once its setup is done, which can end after the fields were filled
                await expect(StorefrontBraintreeCardPayment.loadingHostedFields).toHaveCount(0);
            };
        };

        await use(task);
    },
});
