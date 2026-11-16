import { test as base, type Product } from '@shopware-ag/acceptance-test-suite';

export type ProceedToBraintreeCheckoutTask = Task<[product: Product]>;

/**
 * Extends the acceptance test suite's test instead of Playwright's, because it uses its tasks.
 *
 * Puts the product into the cart and opens the confirm page with the test shipping method
 * and the Braintree payment method selected. The customer must be logged in.
 */
export const ProceedToBraintreeCheckout = base.extend<FixtureTypes>({
    ProceedToBraintreeCheckout: async ({
        ShopCustomer,
        StorefrontProductDetail,
        StorefrontCheckoutConfirm,
        TestShippingMethod,
        AddProductToCart,
        ProceedFromProductToCheckout,
        SelectShippingMethod,
        SelectPaymentMethod,
    }, use) => {
        const task: ProceedToBraintreeCheckoutTask = (product) => {
            return async function ProceedToBraintreeCheckout() {
                await ShopCustomer.goesTo(StorefrontProductDetail.url(product));
                await ShopCustomer.attemptsTo(AddProductToCart(product));
                await ShopCustomer.attemptsTo(ProceedFromProductToCheckout());

                // Both selections reload the confirm page, so they have to happen before anything is filled in
                await ShopCustomer.attemptsTo(SelectShippingMethod(TestShippingMethod.name));
                await ShopCustomer.attemptsTo(SelectPaymentMethod(StorefrontCheckoutConfirm.paymentBraintreeName));
            };
        };

        await use(task);
    },
});
