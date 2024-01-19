import * as sw from '@shopware-ag/meteor-admin-sdk';
import type { Entity } from '@shopware-ag/meteor-admin-sdk/es/data/_internals/Entity';
import type VueI18n from 'vue-i18n';

const addLocations = async (paymentMethod: Entity<'payment_method'>, i18n: VueI18n): Promise<void> => {
    if (sw.location.is(sw.location.MAIN_HIDDEN)) {
        await sw.ui.module.payment.overviewCard.add({
            positionId: 'swag-braintree-app-payment-overview-position',
            paymentMethodHandlers: [
                'handler_app_swagbraintreeapp_credit_card',
            ],
        });

        await sw.ui.componentSection.add({
            component: 'card',
            positionId: 'swag-braintree-app-payment-overview-position',
            props: {
                title: paymentMethod?.translated?.name,
                locationId: 'swag-braintree-app-payment-overview-position-before',
            },
        });

        await sw.ui.settings.addSettingsItem({
            label: i18n.tc('settings.title'),
            locationId: 'swag-braintree-app-settings-position',
            icon: 'default-object-books',
            displaySearchBar: true,
            tab: 'plugins',
        });

        await sw.ui.tabs('sw-order-detail').addTabItem({
            label: i18n.tc('orderTransactionDetail.tabLabel'),
            componentSectionId: 'swag-braintree-app-order-transaction-detail',
        });

        await sw.ui.componentSection.add({
            component: 'card',
            positionId: 'swag-braintree-app-order-transaction-detail',
            props: {
                title: i18n.tc('orderTransactionDetail.title'),
                locationId: 'swag-braintree-app-order-transaction-detail-position-before',
            },
        });
    }
};

export { addLocations };
