<template>
<component :is='page' />
</template>

<script lang='ts'>
import type { Component } from 'vue';
import { defineComponent } from 'vue';
import * as sw from '@shopware-ag/meteor-admin-sdk';
import SwBraintreeAppConfigPage from '@/views/sw-braintree-app-config-page.vue';
import SwBraintreeAppOrderTransactionDetail from '@/views/sw-braintree-app-order-transaction-detail.vue';
import SwBraintreeAppSettingsPage from '@/views/sw-braintree-app-settings-page.vue';

const pages: Record<string, Component> = {
    'swag-braintree-app-payment-overview-position-before': SwBraintreeAppConfigPage,
    'swag-braintree-app-order-transaction-detail-position-before': SwBraintreeAppOrderTransactionDetail,
    'swag-braintree-app-settings-position': SwBraintreeAppSettingsPage,
};

export default defineComponent({
    name: 'sw-braintree-app-page-controller',

    computed: {
        page(): Component | null {
            if (sw.location.is(sw.location.MAIN_HIDDEN))
                return null;

            const location = sw.location.get();

            if (!pages[location])
                throw new Error('Page not found');

            return pages[location];
        },
    },
});
</script>
