import type { I18n } from '@/i18n';
import * as sw from '@shopware-ag/meteor-admin-sdk';

export async function addLocations(paymentMethod: EntitySchema.Entity<'payment_method'>, i18n: I18n) {
    await Promise.all([
        sw.ui.module.payment.overviewCard.add({
            positionId: 'swag-braintree-app-payment-overview-position',
            paymentMethodHandlers: [
                'handler_app_swagbraintreeapp_credit_card',
            ],
        }),

        sw.ui.componentSection.add({
            component: 'card',
            positionId: 'swag-braintree-app-payment-overview-position',
            props: {
                title: paymentMethod.translated?.name,
                locationId: 'swag-braintree-app-payment-overview-position-before',
            },
        }),

        sw.ui.settings.addSettingsItem({
            label: i18n.global.t('settings.title'),
            locationId: 'swag-braintree-app-settings-position',
            // @ts-expect-error - icons are incomplete
            icon: 'regular-bold',
            displaySearchBar: true,
            tab: 'plugins',
        }),

        sw.ui.tabs('sw-order-detail').addTabItem({
            label: i18n.global.t('orderTransactionDetail.tabLabel'),
            componentSectionId: 'swag-braintree-app-order-transaction-detail',
        }),

        sw.ui.componentSection.add({
            component: 'card',
            positionId: 'swag-braintree-app-order-transaction-detail',
            props: {
                title: i18n.global.t('orderTransactionDetail.title'),
                locationId: 'swag-braintree-app-order-transaction-detail-position-before',
            },
        }),
    ]);
}
