import type { FrameLocator } from '@playwright/test';
import type { Page, Locator } from '@shopware-ag/acceptance-test-suite';

/**
 * The Braintree settings module, an admin-sdk iframe of the app server.
 * Its route contains a generated module id, so it is reached through the settings listing.
 */
export class BraintreeSettings {
    public readonly settingsListingLink: Locator;

    private readonly frame: FrameLocator;
    public readonly salesChannelSelect: Locator;
    public readonly saveButton: Locator;

    public readonly generalCardLoader: Locator;
    public readonly threeDSecureEnforcedSwitch: Locator;
    public readonly threeDSecureEnforcedInheritanceToggle: Locator;

    public readonly merchantAccountOptions: Locator;

    constructor(page: Page) {
        this.settingsListingLink = page.locator('.sw-settings__content-grid').getByRole('link', { name: 'Braintree by PayPal' });

        this.frame = page.frameLocator('iframe[src*="location-id=swag-braintree-app-settings-position"]');
        this.salesChannelSelect = this.frame.locator('.sw-braintree-sales-channel-switch__select input');
        this.saveButton = this.frame.getByRole('button', { name: 'Save Settings' });

        const generalCard = this.frame.locator('.sw-braintree-app-settings-general');
        this.generalCardLoader = generalCard.locator('.mt-loader');
        const threeDSecureEnforced = generalCard.locator('.sw-braintree-app-settings-general__threeDSecureEnforced');
        this.threeDSecureEnforcedSwitch = threeDSecureEnforced.getByRole('checkbox', { name: 'Only accept transactions with 3D Secure' });
        this.threeDSecureEnforcedInheritanceToggle = threeDSecureEnforced.locator('.mt-inheritance-switch');

        this.merchantAccountOptions = this.frame.locator('.mt-select-result-list__item-list .mt-select-result');
    }

    settingsListingUrl(): string {
        return '#/sw/settings/index/plugins';
    }

    tab(name: 'General' | 'Currency mapping'): Locator {
        return this.frame.getByRole('tab', { name });
    }

    salesChannelOption(name: string): Locator {
        return this.frame.locator('.mt-select-result').filter({ hasText: name });
    }

    merchantAccountSelect(currencyIsoCode: string): Locator {
        return this.currencyRow(currencyIsoCode).locator('input');
    }

    merchantAccountClearButton(currencyIsoCode: string): Locator {
        return this.currencyRow(currencyIsoCode).getByTestId('select-clear-button');
    }

    private currencyRow(currencyIsoCode: string): Locator {
        return this.frame.getByRole('row', { name: `(${currencyIsoCode})` });
    }
}
