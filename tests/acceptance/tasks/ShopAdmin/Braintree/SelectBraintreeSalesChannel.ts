import { test as base } from '@playwright/test';

export type SelectBraintreeSalesChannelTask = Task<[salesChannelName: string]>;

/** "All channels" selects the default configuration */
export const SelectBraintreeSalesChannel = base.extend<FixtureTypes>({
    SelectBraintreeSalesChannel: async ({ AdminBraintreeSettings }, use) => {
        const task: SelectBraintreeSalesChannelTask = (salesChannelName) => {
            return async function SelectBraintreeSalesChannel() {
                await AdminBraintreeSettings.salesChannelSelect.click();
                await AdminBraintreeSettings.salesChannelOption(salesChannelName).click();
            };
        };

        await use(task);
    },
});
