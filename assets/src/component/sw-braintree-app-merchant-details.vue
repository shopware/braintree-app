<template>
<div class='sw-braintree-merchant-container'>
    <div class='sw-braintree-merchant-container__merchant'>
        <div class='sw-braintree-merchant-container__merchant__id'>
            {{ $t('configuration.merchantId' ) }}: <span class='fw-normal'>{{ merchantDetails }}</span>
        </div>

        <mt-link
            v-if='!!connection?.merchantAccount'
            class='sw-braintree-merchant-container__merchant__disconnect'
            href='javascript:;'
            :disabled='loading'
            @click='$emit("disconnect")'
        >
            {{ $t('register.disconnect-button') }}
        </mt-link>
    </div>

    <sw-status-indicator :status='status' :text='statusText' />
</div>
</template>

<script lang="ts">
import { type PropType, defineComponent } from 'vue';
import SwStatusIndicator from './base/sw-status-indicator.vue';
import { MtLink } from '@shopware-ag/meteor-component-library';

export default defineComponent({
    name: 'sw-braintree-app-merchant-details',

    components: { SwStatusIndicator, MtLink },

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
                ?? this.$t('configuration.merchant.disconnected');
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
            return this.$t(`configuration.status.${this.connectionStatus}`);
        },
    },
});
</script>

<style lang="scss" scoped>
#app .sw-braintree-merchant-container {
    background: var(--color-elevation-surface-sunken);
    border: 1px solid var(--color-border-primary-default);
    border-radius: var(--border-radius-s);
    padding: var(--scale-size-24);

    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;

    &__merchant {
        font-size: var(--font-size-xs);
        line-height: var(--font-line-height-xs);

        &__id {
            color: var(--color-text-primary-default);
            font-weight: var(--font-weight-semi-bold);
        }
    }
}

.fw-normal {
    font-weight: normal;
}
</style>
