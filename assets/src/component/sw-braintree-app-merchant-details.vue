<template>
<div class='sw-braintree-merchant-container'>
    <div class='sw-braintree-merchant-container__merchant'>
        <span class='sw-braintree-merchant-container__merchant__id'>
            {{ $tc('configuration.merchantId' ) }}: <span class='fw-normal'>{{ merchantDetails }}</span>
        </span>

        <sw-internal-link
            v-if='!!connection?.merchantAccount'
            class='sw-braintree-merchant-container__merchant__disconnect'
            href='javascript:;'
            :disabled='loading'
            @click='$emit("disconnect")'
        >
            {{ $t('register.disconnect-button') }}
        </sw-internal-link>
    </div>

    <sw-status-indicator :status='status' :text='statusText' />
</div>
</template>

<script lang="ts">
import { type PropType, defineComponent } from 'vue';
import SwStatusIndicator from './base/sw-status-indicator.vue';
import SwInternalLink from './base/sw-internal-link.vue';

export default defineComponent({
    name: 'sw-braintree-app-merchant-details',

    components: { SwStatusIndicator, SwInternalLink },

    emits: ['disconnect'],

    props: {
        connection: {
            type: Object as PropType<BraintreeConnection>,
            required: false,
            default: undefined,
        },

        loading: {
            type: Boolean,
            required: true,
        },
    },

    computed: {
        connectionStatus(): string {
            return this.connection?.connectionStatus ?? 'disconnected';
        },

        merchantDetails(): string {
            return this.connection?.merchantAccount?.id
                ?? this.$tc('configuration.merchant.disconnected');
        },

        status(): StatusIndicatorType | undefined {
            if (this.connectionStatus === 'active')
                return 'success';

            if (this.connectionStatus === 'pending')
                return 'warning';

            if (this.connectionStatus === 'suspended')
                return 'danger';

            return undefined;
        },

        statusText(): string {
            return this.$tc(`configuration.status.${this.connectionStatus}`);
        },
    },
});
</script>

<style lang="scss" scoped>
.sw-braintree-merchant-container {
    background: #F9FAFB;
    border: 1px solid #D1D9E0;
    border-radius: 4px;
    padding: 32px;

    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;

    &__merchant {
        &__id {
            font-size: 14px;
            line-height: 16px;
            font-style: normal;
            color: #52667A;
            font-weight: 600;
            font-family: Source Sans Pro,Helvetica Neue,Helvetica,Arial,sans-serif;
        }
    }
}

.fw-normal {
    font-weight: normal;
}
</style>
