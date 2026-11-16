import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';

// The tab loads the shop config including the private key from the app server, a trace would record it
test.use({ trace: 'off', screenshot: 'off' });

// "Successful No-Challenge" 3DS card: https://developer.paypal.com/braintree/docs/guides/3d-secure/testing/
const FRICTIONLESS_3DS_CARD = {
    number: '4000000000002701',
    expirationDate: `01/${new Date().getFullYear() + 3}`,
    cvv: '123',
};

test('Merchant sees the Braintree transaction of an order paid with a credit card.', { tag: ['@Administration'] }, async ({
    ShopCustomer,
    ShopAdmin,
    AdminPage,
    AdminApiContext,
    AdminDashboard,
    TestDataService,
    BraintreeDataProvider,
    StorefrontCheckoutFinish,
    AdminOrderDetail,
    AdminBraintreeOrderTransactionDetail,
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
    // Preselects Braintree for the customer: ProceedToBraintreeCheckout may click the payment method
    // before the confirm page's scripts are ready, and then the selection never reaches the shop
    const paymentMethodResponse = await AdminApiContext.post('search-ids/payment-method', {
        data: { filter: [{ type: 'equals', field: 'technicalName', value: 'payment_SwagBraintreeApp_credit_card' }] },
    });
    expect(paymentMethodResponse.ok()).toBeTruthy();
    const { data: [paymentMethodId] } = await paymentMethodResponse.json() as { data: string[] };
    const customer = await TestDataService.createCustomer({ lastPaymentMethodId: paymentMethodId });

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));
    await ShopCustomer.attemptsTo(ConfirmTermsAndConditions());
    await ShopCustomer.attemptsTo(FillBraintreeCard(FRICTIONLESS_3DS_CARD));
    await ShopCustomer.attemptsTo(SubmitOrder());

    const orderId = StorefrontCheckoutFinish.getOrderId();
    expect(orderId).not.toBe('');

    const transactionDetail = AdminBraintreeOrderTransactionDetail;
    // Opening the order detail creates an order version, which blocks the deletion of the test shipping method
    // until the admin deletes it again when leaving the page
    const versionDeleted = AdminPage.waitForResponse(response => response.request().method() === 'POST'
      && new RegExp(`/_action/version/[0-9a-f]{32}/order/${orderId}$`).test(new URL(response.url()).pathname));
    await ShopAdmin.goesTo(AdminOrderDetail.url(orderId));
    try {
        await transactionDetail.tabLink.click();

        await expect(transactionDetail.status).toHaveText('Submitted for settlement');
        await expect(transactionDetail.liabilityShiftPossible).toContainText('Yes');
        await expect(transactionDetail.liabilityShifted).toContainText('Yes');

        await expect(transactionDetail.controlPanelLink).toBeVisible();
        const transactionId = (await transactionDetail.transactionId.innerText()).replace('Transaction ID', '').trim();
        expect(transactionId).not.toBe('');
        // compared as a boolean, a failure message must not print the merchant ID
        const expectedLink = `https://sandbox.braintreegateway.com/merchants/${BraintreeDataProvider.get('MERCHANT_ID')}/transactions/${transactionId}`;
        expect(await transactionDetail.controlPanelLink.getAttribute('href') === expectedLink, 'Link to the transaction in the sandbox control panel').toBe(true);
    }
    finally {
        await ShopAdmin.goesTo(AdminDashboard.url());
        expect((await versionDeleted).ok()).toBeTruthy();
    }
});
