import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';

// "Successful No-Challenge" 3DS card: https://developer.paypal.com/braintree/docs/guides/3d-secure/testing/
const FRICTIONLESS_3DS_CARD = {
    number: '4000000000002701',
    expirationDate: `01/${new Date().getFullYear() + 3}`,
    cvv: '123',
};

test('Customer buys a product and pays with a credit card via Braintree.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
    AdminApiContext,
    TestDataService,
    StorefrontCheckoutFinish,
    Login,
    ProceedToBraintreeCheckout,
    ConfirmTermsAndConditions,
    FillBraintreeCard,
    SubmitOrder,
}) => {
    // The sandbox declines amounts from 2000 and may reject a repeated amount with the same card as duplicate
    const gross = Math.round((10 + Math.random() * 1000) * 100) / 100;
    const net = Math.round(gross / 1.19 * 100) / 100;
    const product = await TestDataService.createBasicProduct({
        price: [TestDataService.defaultCurrencyId, TestDataService.defaultSalesChannel.currencyId]
            .map(currencyId => ({ currencyId, gross, net, linked: false })),
    });
    const customer = await TestDataService.createCustomer();

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));
    await ShopCustomer.attemptsTo(ConfirmTermsAndConditions());
    await ShopCustomer.attemptsTo(FillBraintreeCard(FRICTIONLESS_3DS_CARD));
    await ShopCustomer.attemptsTo(SubmitOrder());

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
