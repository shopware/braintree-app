import { expect, type FixtureTypes } from '@shopware-ag/acceptance-test-suite';
import type { BraintreeAppClient } from './BraintreeAppClient';
import type { BraintreeDataProvider } from './BraintreeDataProvider';

/**
 * The Braintree state every spec starts from: connected with the sandbox keys, the sales channel currency
 * mapped to a merchant account on the default level and 3D Secure not enforced by default.
 * The setup project establishes it, specs that change it restore it in an afterEach hook.
 */
export class BraintreeBaseline {
    constructor(
        private readonly appClient: BraintreeAppClient,
        private readonly dataProvider: BraintreeDataProvider,
        private readonly adminApiContext: FixtureTypes['AdminApiContext'],
        private readonly currencyId: string,
    ) {}

    async establish(): Promise<void> {
        expect(await this.appClient.connect(this.dataProvider.getCredentials()), 'The Braintree sandbox keys were rejected').toBe('active');

        const currency = await this.currency();
        const [merchantAccountId] = await this.merchantAccountIds();
        expect(merchantAccountId, `The Braintree sandbox has no merchant account in ${currency.isoCode}`).toBeDefined();

        await this.appClient.setCurrencyMapping(null, currency, merchantAccountId);
        await this.appClient.setThreeDSecureEnforced(null, false);
    }

    /** The currency of the default sales channel */
    async currency(): Promise<{ id: string, isoCode: string }> {
        const response = await this.adminApiContext.get(`currency/${this.currencyId}`);
        expect(response.ok()).toBeTruthy();

        return (await response.json() as { data: { id: string, isoCode: string } }).data;
    }

    /** The sandbox merchant accounts in the currency of the default sales channel, the first one is the mapped one */
    async merchantAccountIds(): Promise<string[]> {
        const { isoCode } = await this.currency();

        return (await this.appClient.getMerchantAccounts())
            .filter(account => account.currencyIsoCode === isoCode)
            .map(account => account.id);
    }
}
