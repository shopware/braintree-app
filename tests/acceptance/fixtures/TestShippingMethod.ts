import { test as base } from '@playwright/test';
import { expect } from '@shopware-ag/acceptance-test-suite';

const SYSTEM_DEFAULT_CURRENCY_ID = 'b7d2554b0ce847cd82f3ac9bd1c0dfca';

export interface TestShippingMethodTypes {
    TestShippingMethod: { id: string, name: string }
}

/**
 * A free shipping method without any rule, assigned to the default sales channel,
 * so checkout works regardless of the shop's own shipping rules and the order total equals the cart.
 *
 * Orders reference their shipping method with a restricted delete, so teardown removes every order
 * placed with it before the shipping method itself.
 */
export const test = base.extend<FixtureTypes>({
    TestShippingMethod: async ({ AdminApiContext, IdProvider, DefaultSalesChannel }, use) => {
        const { id, uuid } = IdProvider.getIdPair();
        const name = `Braintree test shipping ${id}`;

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

        await use({ id: uuid, name });

        const orderResponse = await AdminApiContext.post('search-ids/order', {
            data: { filter: [{ type: 'equals', field: 'deliveries.shippingMethodId', value: uuid }] },
        });
        expect(orderResponse.ok()).toBeTruthy();
        const { data: orderIds } = await orderResponse.json() as { data: string[] };

        // separate requests: within one sync request the restriction is checked before the orders are gone
        for (const orderId of orderIds) {
            const orderDelete = await AdminApiContext.delete(`order/${orderId}`);
            expect(orderDelete.ok(), await orderDelete.text()).toBeTruthy();
        }

        const shippingMethodDelete = await AdminApiContext.delete(`shipping-method/${uuid}`);
        expect(shippingMethodDelete.ok(), await shippingMethodDelete.text()).toBeTruthy();
    },
});
