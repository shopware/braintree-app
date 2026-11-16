import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';

// The payment overview card shows the private key and the restore hook sends it to the app server
test.use({ trace: 'off', screenshot: 'off' });

// a hook instead of finally: it also runs when the test times out, before the app client is disposed
test.afterEach(async ({ BraintreeBaseline }) => {
    await BraintreeBaseline.establish();
});

test('Merchant maps and unmaps the Braintree merchant account of the sales channel currency.', { tag: ['@Administration'] }, async ({
    ShopAdmin,
    AdminBraintreeSettings,
    AdminBraintreePaymentOverview,
    BraintreeAppClient,
    BraintreeBaseline,
    OpenBraintreeSettings,
    SaveBraintreeSettings,
}) => {
    const currency = await BraintreeBaseline.currency();
    const merchantAccountIds = await BraintreeBaseline.merchantAccountIds();
    const [mappedMerchantAccountId] = merchantAccountIds;

    const merchantAccountSelect = AdminBraintreeSettings.merchantAccountSelect(currency.isoCode);

    // The banner counts mapping rows, so this relies on the default mapping being the shop's only one
    await BraintreeAppClient.deleteCurrencyMapping(null, currency.id);

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
});
