import { test as base, type Locator, type Product } from '@shopware-ag/acceptance-test-suite';

export type ProceedToBraintreeCheckoutTask = Task<[product: Product]>;

/**
 * Extends the acceptance test suite's test instead of Playwright's, because it uses its tasks.
 *
 * Puts the product into the cart and opens the confirm page with the test shipping method
 * and the Braintree payment method selected and its card form loaded. The customer must be logged in.
 */
export const ProceedToBraintreeCheckout = base.extend<FixtureTypes>({
    ProceedToBraintreeCheckout: async ({
        ShopCustomer,
        StorefrontProductDetail,
        StorefrontCheckoutConfirm,
        StorefrontBraintreeCardPayment,
        TestShippingMethod,
        AddProductToCart,
        ProceedFromProductToCheckout,
    }, use) => {
        const page = StorefrontCheckoutConfirm.page;

        /**
         * The confirm page saves a shipping or payment method by submitting its form from the change listener of the
         * storefront's FormAutoSubmit plugin, which is loaded asynchronously after DOMContentLoaded. A selection made
         * earlier only changes the radio in the browser. The plugin registers its instance on the form and attaches the
         * listener in one synchronous step, so a registered instance on both forms means a change will be submitted.
         */
        const selectMethod = async (radio: Locator) => {
            await page.waitForFunction(() => ['changeShippingForm', 'changePaymentForm'].every((id) => {
                const form = document.getElementById(id);
                const pluginManager = (window as unknown as { PluginManager?: { getPluginInstanceFromElement(el: Element, name: string): unknown } }).PluginManager;

                return form && pluginManager?.getPluginInstanceFromElement(form, 'FormAutoSubmit');
            }));

            if (await radio.isChecked()) {
                return;
            }

            // submitting the form reloads the confirm page
            const reloaded = page.waitForEvent('load');
            await radio.click();
            await reloaded;
            // checked in the reloaded page, so the shop saved the selection
            await ShopCustomer.expects(radio).toBeChecked();
        };

        const task: ProceedToBraintreeCheckoutTask = (product) => {
            return async function ProceedToBraintreeCheckout() {
                await ShopCustomer.goesTo(StorefrontProductDetail.url(product));
                await ShopCustomer.attemptsTo(AddProductToCart(product));
                await ShopCustomer.attemptsTo(ProceedFromProductToCheckout());

                // Both selections reload the confirm page, so they have to happen before anything is filled in
                await selectMethod(StorefrontCheckoutConfirm.shippingMethodRadioGroup.getByRole('radio', { name: TestShippingMethod.name }));
                await selectMethod(StorefrontCheckoutConfirm.paymentBraintree);

                // The card form requests an app token from the shop before it creates the Hosted Fields. Without session
                // locking (PHP's Redis session handler by default), a token request still running when the test navigates
                // on writes back an older session and drops what the next page stored in it, e.g. Shopware's notices.
                await ShopCustomer.expects(StorefrontBraintreeCardPayment.cardNumberInput).toBeVisible();
            };
        };

        await use(task);
    },
});
