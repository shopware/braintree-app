import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';

// Restores the connection and the default currency mapping the setup established (disconnecting keeps the mappings).
// A hook instead of finally: it also runs when the test times out, before the app client is disposed.
test.afterEach(async ({ AdminApiContext, TestDataService, BraintreeAppClient, BraintreeDataProvider }) => {
    const status = await BraintreeAppClient.connect({
        merchantId: BraintreeDataProvider.get('MERCHANT_ID'),
        publicKey: BraintreeDataProvider.get('PUBLIC_KEY'),
        privateKey: BraintreeDataProvider.get('PRIVATE_KEY'),
    });
    expect(status).toBe('active');

    const currencyResponse = await AdminApiContext.get(`currency/${TestDataService.defaultSalesChannel.currencyId}`);
    expect(currencyResponse.ok()).toBeTruthy();
    const { data: currency } = await currencyResponse.json() as { data: { id: string, isoCode: string } };
    const merchantAccount = (await BraintreeAppClient.getMerchantAccounts())
        .find(account => account.currencyIsoCode === currency.isoCode);
    await BraintreeAppClient.setCurrencyMapping(null, currency, merchantAccount!.id);
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
