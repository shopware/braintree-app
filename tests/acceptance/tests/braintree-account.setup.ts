import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';

const PAYMENT_METHOD_TECHNICAL_NAME = 'payment_SwagBraintreeApp_credit_card';

test('Braintree Account setup', async ({
    AdminApiContext,
    BraintreeBaseline,
    TestDataService,
}) => {
    const paymentMethodResponse = await AdminApiContext.post('search/payment-method', {
        data: { limit: 1, filter: [{ type: 'equals', field: 'technicalName', value: PAYMENT_METHOD_TECHNICAL_NAME }] },
    });
    expect(paymentMethodResponse.ok()).toBeTruthy();
    const [paymentMethod] = (await paymentMethodResponse.json() as { data: { id: string }[] }).data;
    expect(paymentMethod, `Payment method ${PAYMENT_METHOD_TECHNICAL_NAME} not found. Is the SwagBraintreeApp installed, active and registered with the app server?`).toBeDefined();

    await BraintreeBaseline.establish();

    const paymentMethodUpdate = await AdminApiContext.patch(`payment-method/${paymentMethod.id}`, { data: { active: true } });
    expect(paymentMethodUpdate.ok()).toBeTruthy();

    // keep the assignment for the spec project, which runs after this test's cleanup
    TestDataService.setCleanUp(false);
    await TestDataService.assignSalesChannelPaymentMethod(TestDataService.defaultSalesChannel.id, paymentMethod.id);
});
