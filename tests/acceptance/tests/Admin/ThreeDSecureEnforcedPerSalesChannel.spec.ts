import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';

test('Merchant enforces 3-D Secure for one sales channel and lets it inherit the default again.', { tag: ['@Administration'] }, async ({
    ShopAdmin,
    AdminBraintreeSettings,
    BraintreeAppClient,
    DefaultSalesChannel,
    OpenBraintreeSettings,
    SelectBraintreeSalesChannel,
    SaveBraintreeSettings,
}) => {
    const salesChannel = DefaultSalesChannel.salesChannel;
    const { threeDSecureEnforcedSwitch, threeDSecureEnforcedInheritanceToggle, generalCardLoader } = AdminBraintreeSettings;

    try {
        await ShopAdmin.attemptsTo(OpenBraintreeSettings('General'));
        await ShopAdmin.attemptsTo(SelectBraintreeSalesChannel(salesChannel.name));
        await expect(generalCardLoader).toBeHidden();
        await expect(threeDSecureEnforcedInheritanceToggle).toHaveAccessibleName('Unlink inheritance');
        await expect(threeDSecureEnforcedSwitch).toBeDisabled();
        await expect(threeDSecureEnforcedSwitch).not.toBeChecked();

        await threeDSecureEnforcedInheritanceToggle.click();
        await threeDSecureEnforcedSwitch.check();
        await ShopAdmin.attemptsTo(SaveBraintreeSettings());

        await ShopAdmin.attemptsTo(OpenBraintreeSettings('General'));
        await expect(generalCardLoader).toBeHidden();
        await expect(threeDSecureEnforcedSwitch).not.toBeChecked();
        await ShopAdmin.attemptsTo(SelectBraintreeSalesChannel(salesChannel.name));
        await expect(generalCardLoader).toBeHidden();
        await expect(threeDSecureEnforcedInheritanceToggle).toHaveAccessibleName('Link inheritance');
        await expect(threeDSecureEnforcedSwitch).toBeChecked();

        expect(await BraintreeAppClient.getThreeDSecureEnforced(salesChannel.id)).toBe(true);
        expect(await BraintreeAppClient.getThreeDSecureEnforced(null)).toBe(false);

        await threeDSecureEnforcedInheritanceToggle.click();
        await ShopAdmin.attemptsTo(SaveBraintreeSettings());

        await ShopAdmin.attemptsTo(OpenBraintreeSettings('General'));
        await ShopAdmin.attemptsTo(SelectBraintreeSalesChannel(salesChannel.name));
        await expect(generalCardLoader).toBeHidden();
        await expect(threeDSecureEnforcedInheritanceToggle).toHaveAccessibleName('Unlink inheritance');
        await expect(threeDSecureEnforcedSwitch).not.toBeChecked();

        expect(await BraintreeAppClient.getThreeDSecureEnforced(salesChannel.id)).toBeNull();
    }
    finally {
        await BraintreeAppClient.setThreeDSecureEnforced(salesChannel.id, null);
    }
});
