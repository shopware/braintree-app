import { test as base } from '@playwright/test';

export type SaveBraintreeSettingsTask = Task<[]>;

export const SaveBraintreeSettings = base.extend<FixtureTypes>({
    SaveBraintreeSettings: async ({ ShopAdmin, AdminPage, AdminBraintreeSettings }, use) => {
        const task: SaveBraintreeSettingsTask = () => {
            return async function SaveBraintreeSettings() {
                const saved = AdminPage.waitForResponse(response => response.request().method() === 'PATCH'
                  && new URL(response.url()).pathname.startsWith('/api/entity/'));

                await AdminBraintreeSettings.saveButton.click();

                const response = await saved;
                ShopAdmin.expects(response.ok(), `${response.request().method()} ${new URL(response.url()).pathname} answered ${response.status()}`).toBe(true);
            };
        };

        await use(task);
    },
});
