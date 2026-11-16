import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';

// The card shows the private key, so nothing of the page may be recorded and no assertion may compare key values
test.use({ trace: 'off', screenshot: 'off' });

test('Merchant connects the Braintree account with its API keys and disconnects it.', { tag: ['@Administration'] }, async ({
    ShopAdmin,
    AdminPage,
    AdminApiContext,
    TestDataService,
    AdminBraintreePaymentOverview,
    BraintreeAppClient,
    BraintreeDataProvider,
}) => {
    const overview = AdminBraintreePaymentOverview;
    const credentials = {
        merchantId: BraintreeDataProvider.get('MERCHANT_ID'),
        publicKey: BraintreeDataProvider.get('PUBLIC_KEY'),
        privateKey: BraintreeDataProvider.get('PRIVATE_KEY'),
    };

    const currencyResponse = await AdminApiContext.get(`currency/${TestDataService.defaultSalesChannel.currencyId}`);
    expect(currencyResponse.ok()).toBeTruthy();
    const { data: currency } = await currencyResponse.json() as { data: { id: string, isoCode: string } };
    const merchantAccount = (await BraintreeAppClient.getMerchantAccounts())
        .find(account => account.currencyIsoCode === currency.isoCode);
    expect(merchantAccount).toBeDefined();

    const saveConfiguration = async (keys: typeof credentials) => {
        await overview.merchantIdInput.fill(keys.merchantId);
        await overview.publicKeyInput.fill(keys.publicKey);
        await overview.privateKeyInput.fill(keys.privateKey);

        const saved = AdminPage.waitForResponse(response => response.request().method() === 'PATCH'
          && new URL(response.url()).pathname === '/api/entity/shop');
        await overview.saveButton.click();
        expect((await saved).status(), 'Saving the configuration').toBe(200);
    };

    try {
        await ShopAdmin.goesTo(overview.url());
        await expect(overview.connectionStatus).toHaveText('Connected');

        await saveConfiguration({ merchantId: 'invalid-merchant', publicKey: 'invalid-public-key', privateKey: 'invalid-private-key' });
        await expect(overview.connectionStatus).toHaveText('Disconnected');
        await expect(overview.merchantId).toHaveText('Merchant ID: Not connected');
        await expect(overview.disconnectLink).toBeHidden();
        expect(await BraintreeAppClient.getConnectionStatus()).toBe('disconnected');

        await saveConfiguration(credentials);
        await expect(overview.connectionStatus).toHaveText('Connected');
        await expect(overview.disconnectLink).toBeVisible();
        expect(await BraintreeAppClient.getConnectionStatus()).toBe('active');

        const disconnected = AdminPage.waitForResponse(response => response.request().method() === 'DELETE'
          && new URL(response.url()).pathname === '/api/config');
        await overview.disconnectLink.click();
        expect((await disconnected).status(), 'Disconnecting').toBe(200);
        await expect(overview.connectionStatus).toHaveText('Disconnected');
        await expect(overview.merchantId).toHaveText('Merchant ID: Not connected');
        await expect(overview.disconnectLink).toBeHidden();
        expect(await BraintreeAppClient.getConnectionStatus()).toBe('disconnected');
    }
    finally {
        expect(await BraintreeAppClient.connect(credentials)).toBe('active');
        await BraintreeAppClient.setCurrencyMapping(null, currency, merchantAccount!.id);
        await BraintreeAppClient.setThreeDSecureEnforced(null, false);
    }
});
