import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';
import type { BraintreeCredentials } from '@services/BraintreeAppClient';

// The card shows the private key, so nothing of the page may be recorded and no assertion may compare key values
test.use({ trace: 'off', screenshot: 'off' });

// a hook instead of finally: it also runs when the test times out, before the app client is disposed
test.afterEach(async ({ BraintreeBaseline }) => {
    await BraintreeBaseline.establish();
});

test('Merchant connects the Braintree account with its API keys and disconnects it.', { tag: ['@Administration'] }, async ({
    ShopAdmin,
    AdminPage,
    AdminBraintreePaymentOverview,
    BraintreeAppClient,
    BraintreeDataProvider,
}) => {
    const overview = AdminBraintreePaymentOverview;

    const saveConfiguration = async (keys: BraintreeCredentials) => {
        await overview.merchantIdInput.fill(keys.merchantId);
        await overview.publicKeyInput.fill(keys.publicKey);
        await overview.privateKeyInput.fill(keys.privateKey);

        const saved = AdminPage.waitForResponse(response => response.request().method() === 'PATCH'
          && new URL(response.url()).pathname === '/api/entity/shop');
        await overview.saveButton.click();
        expect((await saved).status(), 'Saving the configuration').toBe(200);
    };

    await ShopAdmin.goesTo(overview.url());
    await expect(overview.connectionStatus).toHaveText('Connected');

    await saveConfiguration({ merchantId: 'invalid-merchant', publicKey: 'invalid-public-key', privateKey: 'invalid-private-key' });
    await expect(overview.connectionStatus).toHaveText('Disconnected');
    await expect(overview.merchantId).toHaveText('Merchant ID: Not connected');
    await expect(overview.disconnectLink).toBeHidden();
    expect(await BraintreeAppClient.getConnectionStatus()).toBe('disconnected');

    await saveConfiguration(BraintreeDataProvider.getCredentials());
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
});
