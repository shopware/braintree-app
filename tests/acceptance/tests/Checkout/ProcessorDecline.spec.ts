import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';

// "Successful No-Challenge" 3DS card: https://developer.paypal.com/braintree/docs/guides/3d-secure/testing/
const FRICTIONLESS_3DS_CARD = {
    number: '4000000000002701',
    expirationDate: `01/${new Date().getFullYear() + 3}`,
    cvv: '123',
};

test('Customer pays with a credit card the processor declines and the payment of the order fails.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
    AdminApiContext,
    TestDataService,
    StorefrontCheckoutConfirm,
    StorefrontCheckoutOrderEdit,
    Login,
    ProceedToBraintreeCheckout,
    ConfirmTermsAndConditions,
    FillBraintreeCard,
}) => {
    // The sandbox declines 2000.00-2999.99 with the processor response of the amount, 2000 is "Do Not Honor":
    // https://developer.paypal.com/braintree/docs/reference/general/testing/
    const gross = Math.round((2000.01 + Math.random() * 0.98) * 100) / 100;
    const product = await TestDataService.createBasicProduct({
        price: [TestDataService.defaultCurrencyId, TestDataService.defaultSalesChannel.currencyId]
            .map(currencyId => ({ currencyId, gross, net: Math.round(gross / 1.19 * 100) / 100, linked: false })),
    });
    const customer = await TestDataService.createCustomer();

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));
    await ShopCustomer.attemptsTo(ConfirmTermsAndConditions());
    await ShopCustomer.attemptsTo(FillBraintreeCard(FRICTIONLESS_3DS_CARD));
    await ShopCustomer.presses(StorefrontCheckoutConfirm.submitOrderButton);

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
