import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';

// Challenge cards: https://developer.paypal.com/braintree/docs/guides/3d-secure/testing/
const expirationDate = `01/${new Date().getFullYear() + 3}`;
const SUCCESSFUL_CHALLENGE_CARD = { number: '4000000000002503', expirationDate, cvv: '123' };
const FAILED_CHALLENGE_CARD = { number: '4000000000002370', expirationDate, cvv: '123' };

test.beforeEach(async ({ BraintreeAppClient, DefaultSalesChannel }) => {
    await BraintreeAppClient.setThreeDSecureEnforced(DefaultSalesChannel.salesChannel.id, true);
});

// a hook instead of finally: it also runs when the test times out, before the app client is disposed
test.afterEach(async ({ BraintreeAppClient, DefaultSalesChannel }) => {
    await BraintreeAppClient.setThreeDSecureEnforced(DefaultSalesChannel.salesChannel.id, null);
});

test('Customer passes an enforced 3D Secure challenge and the order is paid.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
    AdminApiContext,
    TestDataService,
    StorefrontCheckoutConfirm,
    StorefrontCheckoutFinish,
    Login,
    ProceedToBraintreeCheckout,
    ConfirmTermsAndConditions,
    FillBraintreeCard,
    CompleteThreeDSecureChallenge,
}) => {
    const gross = Math.round((10 + Math.random() * 1000) * 100) / 100;
    const product = await TestDataService.createBasicProduct({
        price: [TestDataService.defaultCurrencyId, TestDataService.defaultSalesChannel.currencyId]
            .map(currencyId => ({ currencyId, gross, net: Math.round(gross / 1.19 * 100) / 100, linked: false })),
    });
    const customer = await TestDataService.createCustomer();

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));
    await ShopCustomer.attemptsTo(ConfirmTermsAndConditions());
    await ShopCustomer.attemptsTo(FillBraintreeCard(SUCCESSFUL_CHALLENGE_CARD));
    await ShopCustomer.presses(StorefrontCheckoutConfirm.submitOrderButton);
    await ShopCustomer.attemptsTo(CompleteThreeDSecureChallenge());
    await ShopCustomer.expects(StorefrontCheckoutFinish.headline).toBeVisible();

    const orderId = StorefrontCheckoutFinish.getOrderId();
    expect(orderId).not.toBe('');

    const transactionResponse = await AdminApiContext.post('search/order-transaction', {
        data: {
            filter: [{ type: 'equals', field: 'orderId', value: orderId }],
            associations: { stateMachineState: {} },
        },
    });
    expect(transactionResponse.ok()).toBeTruthy();
    const { data: transactions } = await transactionResponse.json() as { data: { stateMachineState: { technicalName: string } }[] };
    expect(transactions.map(transaction => transaction.stateMachineState.technicalName)).toEqual(['paid']);
});

test('Customer fails an enforced 3D Secure challenge and the payment of the order fails.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
    AdminApiContext,
    TestDataService,
    StorefrontCheckoutConfirm,
    StorefrontCheckoutOrderEdit,
    Login,
    ProceedToBraintreeCheckout,
    ConfirmTermsAndConditions,
    FillBraintreeCard,
    CompleteThreeDSecureChallenge,
}) => {
    const gross = Math.round((10 + Math.random() * 1000) * 100) / 100;
    const product = await TestDataService.createBasicProduct({
        price: [TestDataService.defaultCurrencyId, TestDataService.defaultSalesChannel.currencyId]
            .map(currencyId => ({ currencyId, gross, net: Math.round(gross / 1.19 * 100) / 100, linked: false })),
    });
    const customer = await TestDataService.createCustomer();

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));
    await ShopCustomer.attemptsTo(ConfirmTermsAndConditions());
    await ShopCustomer.attemptsTo(FillBraintreeCard(FAILED_CHALLENGE_CARD));
    await ShopCustomer.presses(StorefrontCheckoutConfirm.submitOrderButton);
    await ShopCustomer.attemptsTo(CompleteThreeDSecureChallenge());

    // Braintree hands out a nonce with status authenticate_failed, which the app rejects because 3D Secure is enforced
    await ShopCustomer.expects(StorefrontCheckoutOrderEdit.paymentFailedAlert).toBeVisible();

    const orderId = StorefrontCheckoutOrderEdit.getOrderId();
    expect(orderId).not.toBe('');

    const transactionResponse = await AdminApiContext.post('search/order-transaction', {
        data: {
            filter: [{ type: 'equals', field: 'orderId', value: orderId }],
            associations: { stateMachineState: {} },
        },
    });
    expect(transactionResponse.ok()).toBeTruthy();
    const { data: transactions } = await transactionResponse.json() as { data: { stateMachineState: { technicalName: string } }[] };
    expect(transactions.map(transaction => transaction.stateMachineState.technicalName)).toEqual(['failed']);
});
