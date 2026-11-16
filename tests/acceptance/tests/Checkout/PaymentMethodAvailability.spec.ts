import { test } from '@fixtures/AcceptanceTest';

// The restore hook sends the API keys to the app server, a trace would record them
test.use({ trace: 'off', screenshot: 'off' });

// a hook instead of finally: it also runs when the test times out, before the app client is disposed
test.afterEach(async ({ BraintreeBaseline }) => {
    await BraintreeBaseline.establish();
});

test('Braintree is not offered in checkout while the shop is disconnected.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
    TestDataService,
    StorefrontCheckoutConfirm,
    BraintreeAppClient,
    Login,
    ProceedToBraintreeCheckout,
}) => {
    const product = await TestDataService.createBasicProduct();
    const customer = await TestDataService.createCustomer();

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));

    await BraintreeAppClient.disconnect();
    await ShopCustomer.goesTo(StorefrontCheckoutConfirm.url());

    await ShopCustomer.expects(StorefrontCheckoutConfirm.paymentBraintreeUnavailableAlert).toBeVisible();
    await ShopCustomer.expects(StorefrontCheckoutConfirm.paymentMethodRadioGroup).toBeVisible();
    await ShopCustomer.expects(StorefrontCheckoutConfirm.paymentBraintree).toHaveCount(0);
});

test('Braintree is not offered in checkout when the sales channel currency has no merchant account.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
    TestDataService,
    StorefrontCheckoutConfirm,
    BraintreeAppClient,
    Login,
    ProceedToBraintreeCheckout,
}) => {
    const product = await TestDataService.createBasicProduct();
    const customer = await TestDataService.createCustomer();

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));

    await BraintreeAppClient.deleteCurrencyMapping(null, TestDataService.defaultSalesChannel.currencyId);
    await ShopCustomer.goesTo(StorefrontCheckoutConfirm.url());

    await ShopCustomer.expects(StorefrontCheckoutConfirm.paymentBraintreeUnavailableAlert).toBeVisible();
    await ShopCustomer.expects(StorefrontCheckoutConfirm.paymentMethodRadioGroup).toBeVisible();
    await ShopCustomer.expects(StorefrontCheckoutConfirm.paymentBraintree).toHaveCount(0);
});
