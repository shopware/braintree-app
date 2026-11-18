import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';
import { createPricedProduct } from '@services/PricedProduct';
import { TestCards } from '@services/TestCards';

// The tab loads the shop config including the private key from the app server, a trace would record it
test.use({ trace: 'off', screenshot: 'off' });

test('Merchant sees the Braintree transaction of an order paid with a credit card.', { tag: ['@Administration'] }, async ({
    ShopCustomer,
    ShopAdmin,
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
    const product = await createPricedProduct(TestDataService, 10, 1010);
    const customer = await TestDataService.createCustomer();

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));
    await ShopCustomer.attemptsTo(ConfirmTermsAndConditions());
    await ShopCustomer.attemptsTo(FillBraintreeCard(TestCards.successfulNoChallenge));
    await ShopCustomer.attemptsTo(SubmitOrder());

    const orderId = StorefrontCheckoutFinish.getOrderId();
    expect(orderId).not.toBe('');

    const transactionDetail = AdminBraintreeOrderTransactionDetail;
    await ShopAdmin.goesTo(AdminOrderDetail.url(orderId));
    await transactionDetail.tabLink.click();

    await expect(transactionDetail.status).toHaveText('Submitted for settlement');
    await expect(transactionDetail.liabilityShiftPossible).toContainText('Yes');
    await expect(transactionDetail.liabilityShifted).toContainText('Yes');

    await expect(transactionDetail.controlPanelLink).toBeVisible();
    const transactionId = (await transactionDetail.transactionId.innerText()).replace('Transaction ID', '').trim();
    expect(transactionId).not.toBe('');
    // compared as a boolean, a failure message must not print the merchant ID
    const expectedLink = `https://sandbox.braintreegateway.com/merchants/${BraintreeDataProvider.getCredentials().merchantId}/transactions/${transactionId}`;
    expect(await transactionDetail.controlPanelLink.getAttribute('href') === expectedLink, 'Link to the transaction in the sandbox control panel').toBe(true);
});
