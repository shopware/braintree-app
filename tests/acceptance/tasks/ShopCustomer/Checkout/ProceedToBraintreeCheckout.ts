import { test as base, type Locator, type Page, type Product } from '@shopware-ag/acceptance-test-suite';

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
                const diagnostics = recordCardFormDiagnostics(page);

                try {
                    await ShopCustomer.goesTo(StorefrontProductDetail.url(product));
                    await ShopCustomer.attemptsTo(AddProductToCart(product));
                    await ShopCustomer.attemptsTo(ProceedFromProductToCheckout());

                    // Both selections reload the confirm page, so they have to happen before anything is filled in
                    await selectMethod(StorefrontCheckoutConfirm.shippingMethodRadioGroup.getByRole('radio', { name: TestShippingMethod.name }));
                    await selectMethod(StorefrontCheckoutConfirm.paymentBraintree);

                    // The card form requests an app token from the shop before it creates the Hosted Fields. Without session
                    // locking (PHP's Redis session handler by default), a token request still running when the test navigates
                    // on writes back an older session and drops what the next page stored in it, e.g. Shopware's notices.
                    // The plugin shows the fields before it creates its 3D Secure client and only then registers its submit
                    // handler. It removes its loader last, so a submit before that would send the order without the card.
                    await ShopCustomer.expects(StorefrontBraintreeCardPayment.cardNumberInput).toBeVisible()
                        .then(() => ShopCustomer.expects(StorefrontBraintreeCardPayment.loadingHostedFields).toHaveCount(0))
                        .catch(async (error: unknown) => { throw await diagnostics.appendTo(error); });
                }
                finally {
                    diagnostics.stop();
                }
            };
        };

        await use(task);
    },
});

const MAX_ENTRIES = 5;
const truncate = (text: string) => text.length > 200 ? `${text.slice(0, 200)}…` : text;
const withoutQuery = (url: string) => url.split(/[?#]/)[0];

/**
 * Collects what tells the causes of a missing card form apart, for the error message of a failed checkout.
 * The message lands in public CI logs, so it holds no request or response bodies, headers or option values
 * besides the app server URL.
 */
function recordCardFormDiagnostics(page: Page) {
    const pageErrors: string[] = [];
    const consoleErrors: string[] = [];
    const failedRequests: string[] = [];
    let appScript = 'not requested';

    const onPageError = (error: Error) => pageErrors.push(truncate(error.message));
    const onConsole = (message: { type(): string, text(): string }) => {
        if (message.type() === 'error') {
            consoleErrors.push(truncate(message.text()));
        }
    };
    const onResponse = (response: { status(): number, url(): string, request(): { method(): string } }) => {
        if (response.url().includes('swag-braintree-app.js')) {
            appScript = `HTTP ${response.status()}`;
        }
        if (response.status() >= 400) {
            failedRequests.push(`${response.request().method()} ${withoutQuery(response.url())} -> HTTP ${response.status()}`);
        }
    };
    const onRequestFailed = (request: { method(): string, url(): string, failure(): { errorText: string } | null }) => {
        if (request.url().includes('swag-braintree-app.js')) {
            appScript = `failed: ${request.failure()?.errorText}`;
        }
        failedRequests.push(`${request.method()} ${withoutQuery(request.url())} -> ${request.failure()?.errorText}`);
    };

    page.on('pageerror', onPageError);
    page.on('console', onConsole);
    page.on('response', onResponse);
    page.on('requestfailed', onRequestFailed);

    return {
        stop() {
            page.off('pageerror', onPageError);
            page.off('console', onConsole);
            page.off('response', onResponse);
            page.off('requestfailed', onRequestFailed);
        },

        async appendTo(error: unknown): Promise<unknown> {
            if (!(error instanceof Error)) {
                return error;
            }

            const state = await page.evaluate(() => {
                const container = document.querySelector('[data-swag-braintree-hosted-fields]');
                let options: { baseUrl?: unknown, appShopId?: unknown } = {};
                try {
                    options = JSON.parse(container?.getAttribute('data-swag-braintree-hosted-fields-options') ?? '{}') as typeof options;
                }
                catch {
                    // reported as missing options below
                }

                const pluginManager = (window as unknown as { PluginManager?: {
                    getPlugin(name: string): unknown
                    getPluginInstanceFromElement(el: Element, name: string): unknown
                } }).PluginManager;
                let pluginRegistered = false;
                try {
                    pluginRegistered = !!pluginManager?.getPlugin('SwagBraintreeHostedFields');
                }
                catch {
                    // getPlugin throws for an unknown plugin
                }

                const checkedPayment = document.querySelector<HTMLInputElement>('input[name="paymentMethodId"]:checked');

                return {
                    container: !!container,
                    baseUrl: typeof options.baseUrl === 'string' ? options.baseUrl : String(options.baseUrl),
                    appShopIdSet: !!options.appShopId,
                    iframes: container?.querySelectorAll('iframe[name^="braintree-hosted-field-"]').length ?? 0,
                    loaderShowing: !!container?.classList.contains('has-element-loader'),
                    pluginRegistered,
                    pluginInitialized: !!(container && pluginManager?.getPluginInstanceFromElement(container, 'SwagBraintreeHostedFields')),
                    // the label also contains the card form, its first line is the payment method's name
                    checkedPayment: checkedPayment ? document.querySelector<HTMLElement>(`label[for="${checkedPayment.id}"]`)?.innerText.split('\n').map(line => line.trim()).find(Boolean) ?? checkedPayment.id : 'none',
                    alerts: [...document.querySelectorAll<HTMLElement>('[role="alert"]')]
                        .filter(alert => alert.offsetParent !== null)
                        .map(alert => alert.innerText.trim()),
                };
            }).catch((evaluateError: unknown) => `page not inspectable: ${String(evaluateError)}`);

            const list = (entries: string[]) => entries.length ? entries.slice(0, MAX_ENTRIES).join(' | ') : 'none';
            const lines = typeof state === 'string'
                ? [state]
                : [
                    `card form container: ${state.container ? `present, baseUrl ${state.baseUrl}, appShopId ${state.appShopIdSet ? 'set' : 'missing'}, ${state.iframes} Hosted Fields iframes, loader ${state.loaderShowing ? 'showing' : 'gone'}` : 'missing'}`,
                    `card form plugin: ${state.pluginRegistered ? 'registered' : 'not registered'}, ${state.pluginInitialized ? 'initialized on the container' : 'not initialized on the container'}`,
                    `checked payment method: ${truncate(state.checkedPayment)}`,
                    `visible alerts: ${list(state.alerts.map(truncate))}`,
                ];
            lines.push(
                `app storefront script: ${appScript}`,
                `page errors: ${list(pageErrors)}`,
                `console errors: ${list(consoleErrors)}`,
                `failed requests: ${list(failedRequests)}`,
            );

            const original = error.message;
            error.message = `${original}\n\nCard form diagnostics:\n${lines.map(line => `  ${line}`).join('\n')}`;
            error.stack = error.stack?.replace(original, error.message);

            return error;
        },
    };
}
