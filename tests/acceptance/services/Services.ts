import { test as base } from '@playwright/test';
import { BraintreeAppClient } from './BraintreeAppClient';
import { BraintreeDataProvider } from './BraintreeDataProvider';

export interface ServicesTypes {
    BraintreeAppClient: BraintreeAppClient
    BraintreeDataProvider: BraintreeDataProvider
}

export const test = base.extend<FixtureTypes>({
    BraintreeAppClient: async ({ AdminApiContext }, use) => {
        const client = await BraintreeAppClient.create(AdminApiContext);
        await use(client);
        await client.dispose();
    },

    BraintreeDataProvider: async ({}, use) => {
        await use(new BraintreeDataProvider());
    },
});
