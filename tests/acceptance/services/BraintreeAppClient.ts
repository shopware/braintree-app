import { request, type APIRequestContext, type APIResponse } from '@playwright/test';
import type { FixtureTypes } from '@shopware-ag/acceptance-test-suite';

const APP_NAME = 'SwagBraintreeApp';

/** "active" once the app finds the default merchant account with the stored credentials */
type ConnectionStatus = 'active' | 'disconnected';

export interface BraintreeCredentials {
    merchantId: string
    publicKey: string
    privateKey: string
}

interface MerchantAccount {
    id: string
    currencyIsoCode: string
}

interface ConfigEntity {
    id: string | null
    salesChannelId: string | null
    threeDSecureEnforced: boolean | null
}

interface CurrencyMappingEntity {
    id: string
    salesChannelId: string | null
    currencyId: string
    currencyIso: string
    merchantAccountId: string | null
}

/**
 * Talks to the Braintree app server like its admin iframe does, so tests can arrange and restore its state.
 * `salesChannelId: null` addresses the default configuration all sales channels inherit from.
 */
export class BraintreeAppClient {
    private constructor(private readonly context: APIRequestContext) {}

    /**
     * Opens an app session for the shop: Shopware signs the admin-sdk URL, and requesting it makes the
     * app server store the shop in the session that every following API request is resolved from.
     */
    static async create(adminApiContext: FixtureTypes['AdminApiContext']): Promise<BraintreeAppClient> {
        const appResponse = await adminApiContext.post('search/app', {
            data: { limit: 1, filter: [{ type: 'equals', field: 'name', value: APP_NAME }] },
        });
        await assertOk(appResponse, `Searching the ${APP_NAME} app`);
        const app = (await appResponse.json() as { data: { baseAppUrl: string | null }[] }).data[0];

        if (!app?.baseAppUrl) {
            throw new Error(`The ${APP_NAME} app is not installed or has no admin module (baseAppUrl).`);
        }

        const origin = new URL(app.baseAppUrl).origin;
        const signResponse = await adminApiContext.post('_action/extension-sdk/sign-uri', {
            data: { appName: APP_NAME, uri: `${origin}/admin-sdk` },
        });
        await assertOk(signResponse, 'Signing the admin-sdk URL');
        const { uri } = await signResponse.json() as { uri: string };

        const context = await request.newContext({ baseURL: origin });
        await assertOk(await context.get(uri), `Opening an app session at ${origin}`);

        return new BraintreeAppClient(context);
    }

    async connect(credentials: BraintreeCredentials): Promise<ConnectionStatus> {
        const response = await this.context.patch('/api/entity/shop', {
            data: {
                braintreeMerchantId: credentials.merchantId,
                braintreePublicKey: credentials.publicKey,
                braintreePrivateKey: credentials.privateKey,
                braintreeSandbox: true,
            },
        });
        // never include the response or request in the message, they would leak the keys
        await assertOk(response, 'Connecting the Braintree account', false);

        return (await response.json() as { connectionStatus: ConnectionStatus }).connectionStatus;
    }

    /** Removes the credentials; currency mappings and configs stay in the app's database */
    async disconnect(): Promise<void> {
        await assertOk(await this.context.delete('/api/config'), 'Disconnecting the Braintree account');
    }

    async getConnectionStatus(): Promise<ConnectionStatus> {
        return (await this.get<{ connectionStatus: ConnectionStatus }>('/api/config/status')).connectionStatus;
    }

    async getMerchantAccounts(): Promise<MerchantAccount[]> {
        return this.get<MerchantAccount[]>('/api/braintree/merchant_accounts');
    }

    async setCurrencyMapping(
        salesChannelId: string | null,
        currency: { id: string, isoCode: string },
        merchantAccountId: string,
    ): Promise<void> {
        // reuse the existing row, the unique constraint does not catch duplicates with salesChannelId null
        const existing = await this.findCurrencyMapping(salesChannelId, currency.id);

        await this.patchCurrencyMappings({
            upsert: [{
                ...(existing ? { id: existing.id } : {}),
                salesChannelId,
                currencyId: currency.id,
                currencyIso: currency.isoCode,
                merchantAccountId,
            }],
            deleted: [],
        });
    }

    async deleteCurrencyMapping(salesChannelId: string | null, currencyId: string): Promise<void> {
        const existing = await this.findCurrencyMapping(salesChannelId, currencyId);

        if (existing) {
            await this.patchCurrencyMappings({ upsert: [], deleted: [existing.id] });
        }
    }

    /**
     * `null` removes the override of a sales channel, so it inherits the default again.
     * The default configuration (salesChannelId null) requires a boolean.
     */
    async setThreeDSecureEnforced(salesChannelId: string | null, enforced: boolean | null): Promise<void> {
        const { id } = await this.get<ConfigEntity>(`/api/entity/by-sales-channel/config/${String(salesChannelId)}`);

        const response = await this.context.patch('/api/entity/config', {
            data: [{ ...(id ? { id } : {}), salesChannelId, threeDSecureEnforced: enforced }],
        });
        await assertOk(response, 'Saving the Braintree config');
    }

    /** `null` means the sales channel inherits the default */
    async getThreeDSecureEnforced(salesChannelId: string | null): Promise<boolean | null> {
        return (await this.get<ConfigEntity>(`/api/entity/by-sales-channel/config/${String(salesChannelId)}`)).threeDSecureEnforced;
    }

    async dispose(): Promise<void> {
        await this.context.dispose();
    }

    private async findCurrencyMapping(salesChannelId: string | null, currencyId: string): Promise<CurrencyMappingEntity | undefined> {
        const mappings = await this.get<CurrencyMappingEntity[]>(`/api/entity/by-sales-channel/currency_mapping/${String(salesChannelId)}`);

        return mappings.find(mapping => mapping.currencyId === currencyId);
    }

    private async patchCurrencyMappings(data: { upsert: Partial<CurrencyMappingEntity>[], deleted: string[] }): Promise<void> {
        await assertOk(await this.context.patch('/api/entity/currency_mapping', { data }), 'Saving the currency mappings');
    }

    private async get<T>(path: string): Promise<T> {
        const response = await this.context.get(path);
        await assertOk(response, `GET ${path}`);

        return await response.json() as T;
    }
}

async function assertOk(response: APIResponse, action: string, withBody = true): Promise<void> {
    if (response.ok()) {
        return;
    }

    const body = withBody ? `: ${(await response.text()).slice(0, 500)}` : '';
    throw new Error(`${action} failed with HTTP ${response.status()}${body}`);
}
