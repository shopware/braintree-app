import type { Page, Locator } from '@shopware-ag/acceptance-test-suite';

/**
 * The Braintree card of the payment overview, an admin-sdk iframe of the app server.
 * It shows the private key in plain text: tests using it must not record traces or screenshots.
 */
export class BraintreePaymentOverview {
    public readonly missingCurrencyMappingsBanner: Locator;
    public readonly merchantId: Locator;
    public readonly connectionStatus: Locator;
    public readonly disconnectLink: Locator;
    public readonly merchantIdInput: Locator;
    public readonly publicKeyInput: Locator;
    public readonly privateKeyInput: Locator;
    public readonly saveButton: Locator;

    constructor(page: Page) {
        const frame = page.frameLocator('iframe[src*="location-id=swag-braintree-app-payment-overview-position-before"]');

        this.missingCurrencyMappingsBanner = frame.locator('.mt-banner').filter({ hasText: 'Missing currency mappings' });
        this.merchantId = frame.locator('.sw-braintree-merchant-container__merchant__id');
        this.connectionStatus = frame.locator('.sw-braintree-merchant-container .sw-status__text');
        this.disconnectLink = frame.getByText('Disconnect', { exact: true });
        this.merchantIdInput = frame.getByLabel('Braintree merchant ID');
        this.publicKeyInput = frame.getByLabel('Braintree public key');
        this.privateKeyInput = frame.getByLabel('Braintree private key');
        this.saveButton = frame.getByRole('button', { name: 'Save configuration' });
    }

    url(): string {
        return '#/sw/settings/payment/overview';
    }
}
