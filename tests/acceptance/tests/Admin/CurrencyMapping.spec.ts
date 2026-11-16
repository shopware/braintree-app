import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';

// The payment overview card shows the private key and the app server returns it to the admin
test.use({ trace: 'off', screenshot: 'off' });

test('Merchant maps and unmaps the Braintree merchant account of the sales channel currency.', { tag: ['@Administration'] }, async ({
    ShopAdmin,
    AdminApiContext,
    TestDataService,
    AdminBraintreeSettings,
    AdminBraintreePaymentOverview,
    BraintreeAppClient,
    OpenBraintreeSettings,
    SaveBraintreeSettings,
}) => {
    const currencyResponse = await AdminApiContext.get(`currency/${TestDataService.defaultSalesChannel.currencyId}`);
    expect(currencyResponse.ok()).toBeTruthy();
    const { data: currency } = await currencyResponse.json() as { data: { id: string, isoCode: string } };
    const merchantAccountIds = (await BraintreeAppClient.getMerchantAccounts())
        .filter(account => account.currencyIsoCode === currency.isoCode)
        .map(account => account.id);
    // the account the setup maps
    const mappedMerchantAccountId = merchantAccountIds[0];

    const merchantAccountSelect = AdminBraintreeSettings.merchantAccountSelect(currency.isoCode);

    // The banner counts mapping rows, so this relies on the default mapping being the shop's only one
    await BraintreeAppClient.deleteCurrencyMapping(null, currency.id);
    try {
        await ShopAdmin.goesTo(AdminBraintreePaymentOverview.url());
        await expect(AdminBraintreePaymentOverview.missingCurrencyMappingsBanner).toBeVisible();

        await ShopAdmin.attemptsTo(OpenBraintreeSettings('Currency mapping'));
        await expect(merchantAccountSelect).toBeEnabled();
        await expect(merchantAccountSelect).toHaveValue('');
        await merchantAccountSelect.click();
        await expect(AdminBraintreeSettings.merchantAccountOptions).toHaveText(merchantAccountIds);
        await AdminBraintreeSettings.merchantAccountOptions.getByText(mappedMerchantAccountId, { exact: true }).click();
        await ShopAdmin.attemptsTo(SaveBraintreeSettings());

        await ShopAdmin.attemptsTo(OpenBraintreeSettings('Currency mapping'));
        await expect(merchantAccountSelect).toBeEnabled();
        await expect(merchantAccountSelect).toHaveValue(mappedMerchantAccountId);

        await ShopAdmin.goesTo(AdminBraintreePaymentOverview.url());
        // the card's inputs are enabled once it loaded the shop, which decides about the banner
        await expect(AdminBraintreePaymentOverview.merchantIdInput).toBeEnabled();
        await expect(AdminBraintreePaymentOverview.missingCurrencyMappingsBanner).toBeHidden();

        // Clearing keeps the mapping row with an empty merchant account, so the banner stays hidden after it
        await ShopAdmin.attemptsTo(OpenBraintreeSettings('Currency mapping'));
        await expect(merchantAccountSelect).toBeEnabled();
        await AdminBraintreeSettings.merchantAccountClearButton(currency.isoCode).click();
        await ShopAdmin.attemptsTo(SaveBraintreeSettings());

        await ShopAdmin.attemptsTo(OpenBraintreeSettings('Currency mapping'));
        await expect(merchantAccountSelect).toBeEnabled();
        await expect(merchantAccountSelect).toHaveValue('');
    }
    finally {
        await BraintreeAppClient.setCurrencyMapping(null, currency, mappedMerchantAccountId);
    }
});
