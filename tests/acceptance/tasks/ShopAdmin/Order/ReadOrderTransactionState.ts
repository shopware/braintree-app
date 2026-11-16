import { test as base, expect } from '@shopware-ag/acceptance-test-suite';

export type ReadOrderTransactionStateTask = (orderId: string) => () => Promise<string>;

/** Returns the technical name of the state of the order's only transaction */
export const ReadOrderTransactionState = base.extend<FixtureTypes>({
    ReadOrderTransactionState: async ({ AdminApiContext }, use) => {
        const task: ReadOrderTransactionStateTask = (orderId) => {
            return async function ReadOrderTransactionState() {
                const response = await AdminApiContext.post('search/order-transaction', {
                    data: {
                        filter: [{ type: 'equals', field: 'orderId', value: orderId }],
                        associations: { stateMachineState: {} },
                    },
                });
                expect(response.ok()).toBeTruthy();

                const { data: transactions } = await response.json() as { data: { stateMachineState: { technicalName: string } }[] };
                expect(transactions, `Transactions of order ${orderId}`).toHaveLength(1);

                return transactions[0].stateMachineState.technicalName;
            };
        };

        await use(task);
    },
});
