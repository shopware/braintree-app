import type { BraintreeCard } from '@tasks/ShopCustomer/Checkout/FillBraintreeCard';

// https://developer.paypal.com/braintree/docs/guides/3d-secure/testing/ asks for an expiry in three years
const expirationDate = `01/${new Date().getFullYear() + 3}`;

/** Braintree's 3D Secure sandbox cards: https://developer.paypal.com/braintree/docs/guides/3d-secure/testing/ */
export const TestCards = {
    successfulNoChallenge: { number: '4000000000002701', expirationDate, cvv: '123' },
    successfulChallenge: { number: '4000000000002503', expirationDate, cvv: '123' },
    failedChallenge: { number: '4000000000002370', expirationDate, cvv: '123' },
} satisfies Record<string, BraintreeCard>;
