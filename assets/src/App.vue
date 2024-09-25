<template>
<component :is='page' />
</template>

<script lang='ts'>
import type { Component } from 'vue';
import { defineComponent, defineAsyncComponent } from 'vue';
import * as sw from '@shopware-ag/meteor-admin-sdk';

const pages: Record<string, Component> = {
    'swag-braintree-app-payment-overview-position-before': defineAsyncComponent(() => import('@/views/sw-braintree-app-config-page.vue')),
    'swag-braintree-app-order-transaction-detail-position-before': defineAsyncComponent(() => import('@/views/sw-braintree-app-order-transaction-detail.vue')),
    'swag-braintree-app-settings-position': defineAsyncComponent(() => import('@/views/sw-braintree-app-settings-page.vue')),
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
