import { test as base } from '@playwright/test';
import { BraintreeAppClient } from './BraintreeAppClient';
import { BraintreeBaseline } from './BraintreeBaseline';
import { BraintreeDataProvider } from './BraintreeDataProvider';

export interface ServicesTypes {
    BraintreeAppClient: BraintreeAppClient
    BraintreeBaseline: BraintreeBaseline
    BraintreeDataProvider: BraintreeDataProvider
    RedactBraintreeCredentials: void
}

export const test = base.extend<FixtureTypes>({
    BraintreeAppClient: async ({ AdminApiContext }, use) => {
        const client = await BraintreeAppClient.create(AdminApiContext);
        await use(client);
        await client.dispose();
    },

    BraintreeBaseline: async ({ BraintreeAppClient, BraintreeDataProvider, AdminApiContext, DefaultSalesChannel }, use) => {
        await use(new BraintreeBaseline(BraintreeAppClient, BraintreeDataProvider, AdminApiContext, DefaultSalesChannel.salesChannel.currencyId));
    },

    BraintreeDataProvider: async ({}, use) => {
        await use(new BraintreeDataProvider());
    },

    /**
     * Playwright writes the errors of a failed test to error-context.md in test-results/, which CI uploads. A failed
     * locator assertion adds the aria snapshot of its frame and a preview of the element, and the Braintree admin
     * pages show the API keys and link the merchant ID. This teardown runs before Playwright writes the file.
     */
    RedactBraintreeCredentials: [async ({}, use, testInfo) => {
        await use();

        const secrets = ['BRAINTREE_MERCHANT_ID', 'BRAINTREE_PUBLIC_KEY', 'BRAINTREE_PRIVATE_KEY']
            .map(name => process.env[name])
            .filter((value): value is string => !!value);

        for (const error of testInfo.errors as Record<string, unknown>[]) {
            for (const [key, value] of Object.entries(error)) {
                if (typeof value === 'string') {
                    error[key] = secrets.reduce((text, secret) => text.replaceAll(secret, '[redacted]'), value);
                }
            }
        }
    }, { auto: true }],
});
