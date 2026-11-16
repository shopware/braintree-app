import { test as base } from '@playwright/test';
import { expect } from '@shopware-ag/acceptance-test-suite';

const SYSTEM_DEFAULT_CURRENCY_ID = 'b7d2554b0ce847cd82f3ac9bd1c0dfca';

export interface TestShippingMethodTypes {
    TestShippingMethod: { name: string }
}

/**
 * A free shipping method without any rule, assigned to the default sales channel,
 * so checkout works regardless of the shop's own shipping rules and the order total equals the cart.
 *
 * Orders reference their shipping method with a restricted delete, so teardown removes every order
 * placed with it before the shipping method itself, including the order versions the administration
 * creates when it opens an order.
 */
export const test = base.extend<FixtureTypes>({
    TestShippingMethod: async ({ AdminApiContext, IdProvider, DefaultSalesChannel }, use) => {
        const { id, uuid } = IdProvider.getIdPair();
        const name = `Braintree test shipping ${id}`;
        const createdAt = new Date().toISOString();

        const deliveryTimeResponse = await AdminApiContext.post('search/delivery-time', { data: { limit: 1 } });
        expect(deliveryTimeResponse.ok()).toBeTruthy();
        const [deliveryTime] = (await deliveryTimeResponse.json() as { data: { id: string }[] }).data;

        const createResponse = await AdminApiContext.post('shipping-method', {
            data: {
                id: uuid,
                name,
                technicalName: `braintree_test_shipping_${id}`,
                active: true,
                deliveryTimeId: deliveryTime.id,
                prices: [{
                    calculation: 1,
                    quantityStart: 1,
                    currencyPrice: [{ currencyId: SYSTEM_DEFAULT_CURRENCY_ID, gross: 0, net: 0, linked: false }],
                }],
                salesChannels: [{ id: DefaultSalesChannel.salesChannel.id }],
            },
        });
        expect(createResponse.ok(), await createResponse.text()).toBeTruthy();

        await use({ name });

        const orderIds = await searchIds(AdminApiContext, 'order', { type: 'equals', field: 'deliveries.shippingMethodId', value: uuid });
        // only versions created during this test can contain its orders
        const versionIds = orderIds.length ? await searchIds(AdminApiContext, 'version', { type: 'range', field: 'createdAt', parameters: { gte: createdAt } }) : [];

        for (const orderId of orderIds) {
            for (const versionId of versionIds) {
                // a version without its own copy of the order returns the live one
                const versionedOrder = await AdminApiContext.get(`order/${orderId}`, { headers: { 'sw-version-id': versionId, Accept: 'application/json' } });
                expect(versionedOrder.ok()).toBeTruthy();
                if ((await versionedOrder.json() as { data: { versionId: string } }).data.versionId === versionId) {
                    const versionDelete = await AdminApiContext.post(`_action/version/${versionId}/order/${orderId}`);
                    expect(versionDelete.ok(), await versionDelete.text()).toBeTruthy();
                }
            }

            const orderDelete = await AdminApiContext.delete(`order/${orderId}`);
            expect(orderDelete.ok(), await orderDelete.text()).toBeTruthy();
        }

        const shippingMethodDelete = await AdminApiContext.delete(`shipping-method/${uuid}`);
        expect(shippingMethodDelete.ok(), await shippingMethodDelete.text()).toBeTruthy();
    },
});

async function searchIds(adminApiContext: FixtureTypes['AdminApiContext'], entity: string, filter: Record<string, unknown>): Promise<string[]> {
    const response = await adminApiContext.post(`search-ids/${entity}`, { data: { filter: [filter] } });
    expect(response.ok()).toBeTruthy();

    return (await response.json() as { data: string[] }).data;
}
