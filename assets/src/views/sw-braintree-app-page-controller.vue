<template>
<component :is='page' />
</template>

<script lang='ts'>
import type { AsyncComponent } from 'vue';
import { defineComponent } from 'vue';
import * as sw from '@shopware-ag/admin-extension-sdk';

export default defineComponent({
    name: 'sw-braintree-app-page-controller',

    data(): {
        pages: Record<string, AsyncComponent>,
    } {
        return {
            pages: {
                'swag-braintree-app-payment-overview-position-before': () => import('./sw-braintree-app-config-page.vue'),
                'swag-braintree-app-order-transaction-detail-position-before': () => import('./sw-braintree-app-order-transaction-detail.vue'),
                'swag-braintree-app-settings-position': () => import('./sw-braintree-app-settings-page.vue'),
            },
        };
    },

    computed: {
        page(): AsyncComponent | null {
            if (sw.location.is(sw.location.MAIN_HIDDEN))
                return null;

            const location = sw.location.get();

            if (!this.pages[location])
                throw new Error('Page not found');

            return this.pages[location];
        },
    },
});
</script>
