import { test as base } from '@playwright/test';
import { BraintreePaymentOverview } from './Administration/BraintreePaymentOverview';
import { BraintreeSettings } from './Administration/BraintreeSettings';
import { BraintreeOrderTransactionDetail } from './Administration/BraintreeOrderTransactionDetail';

export interface AdminPageTypes {
    AdminBraintreePaymentOverview: BraintreePaymentOverview
    AdminBraintreeSettings: BraintreeSettings
    AdminBraintreeOrderTransactionDetail: BraintreeOrderTransactionDetail
}

export const test = base.extend<FixtureTypes>({
    AdminBraintreePaymentOverview: async ({ AdminPage }, use) => {
        await use(new BraintreePaymentOverview(AdminPage));
    },

    AdminBraintreeSettings: async ({ AdminPage }, use) => {
        await use(new BraintreeSettings(AdminPage));
    },

    AdminBraintreeOrderTransactionDetail: async ({ AdminPage }, use) => {
        await use(new BraintreeOrderTransactionDetail(AdminPage));
    },
});
