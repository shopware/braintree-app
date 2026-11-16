import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';

const PAYMENT_METHOD_TECHNICAL_NAME = 'payment_SwagBraintreeApp_credit_card';

test('Braintree Account setup', async ({
    AdminApiContext,
    BraintreeAppClient,
    BraintreeDataProvider,
    TestDataService,
}) => {
    const paymentMethodResponse = await AdminApiContext.post('search/payment-method', {
        data: { limit: 1, filter: [{ type: 'equals', field: 'technicalName', value: PAYMENT_METHOD_TECHNICAL_NAME }] },
    });
    expect(paymentMethodResponse.ok()).toBeTruthy();
    const [paymentMethod] = (await paymentMethodResponse.json() as { data: { id: string }[] }).data;
    expect(paymentMethod, `Payment method ${PAYMENT_METHOD_TECHNICAL_NAME} not found. Is the SwagBraintreeApp installed, active and registered with the app server?`).toBeDefined();

    const status = await BraintreeAppClient.connect({
        merchantId: BraintreeDataProvider.get('MERCHANT_ID'),
        publicKey: BraintreeDataProvider.get('PUBLIC_KEY'),
        privateKey: BraintreeDataProvider.get('PRIVATE_KEY'),
    });
    expect(status, 'The Braintree sandbox credentials were rejected').toBe('active');

    const currencyResponse = await AdminApiContext.get(`currency/${TestDataService.defaultSalesChannel.currencyId}`);
    expect(currencyResponse.ok()).toBeTruthy();
    const { data: currency } = await currencyResponse.json() as { data: { id: string, isoCode: string } };
    const merchantAccount = (await BraintreeAppClient.getMerchantAccounts())
        .find(account => account.currencyIsoCode === currency.isoCode);
    expect(merchantAccount, `The Braintree sandbox has no merchant account in ${currency.isoCode}`).toBeDefined();

    await BraintreeAppClient.setCurrencyMapping(null, currency, merchantAccount!.id);
    await BraintreeAppClient.setThreeDSecureEnforced(null, false);

    const paymentMethodUpdate = await AdminApiContext.patch(`payment-method/${paymentMethod.id}`, { data: { active: true } });
    expect(paymentMethodUpdate.ok()).toBeTruthy();

    // keep the assignment for the spec project, which runs after this test's cleanup
    TestDataService.setCleanUp(false);
    await TestDataService.assignSalesChannelPaymentMethod(TestDataService.defaultSalesChannel.id, paymentMethod.id);
});
