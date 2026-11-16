import { test as base } from '@playwright/test';

export type OpenBraintreeSettingsTask = Task<[tab: 'General' | 'Currency mapping']>;

/**
 * Opens the settings module freshly, so it shows what the app server has stored.
 *
 * The app remembers the last tab, and clicking the tab that is already active drops the
 * registered save handlers, after which "Save Settings" does nothing. So only an inactive tab is clicked.
 */
export const OpenBraintreeSettings = base.extend<FixtureTypes>({
    OpenBraintreeSettings: async ({ ShopAdmin, AdminBraintreeSettings }, use) => {
        const task: OpenBraintreeSettingsTask = (tab) => {
            return async function OpenBraintreeSettings() {
                await ShopAdmin.goesTo(AdminBraintreeSettings.url());
                await AdminBraintreeSettings.settingsListingLink.click();

                const tabLink = AdminBraintreeSettings.tab(tab);
                await ShopAdmin.expects(tabLink).toBeVisible();

                if (await tabLink.getAttribute('aria-selected') !== 'true') {
                    await tabLink.click();
                }
                await ShopAdmin.expects(tabLink).toHaveAttribute('aria-selected', 'true');
            };
        };

        await use(task);
    },
});
