<template>
<div class='sw-braintree-payment-method'>
    <div class='sw-braintree-payment-method__title'>
        {{ $t('configuration.payment.title') }}
    </div>

    <div class='sw-braintree-payment-method__card'>
        <div class='sw-braintree-payment-method__card__method'>
            <img class='sw-braintree-payment-method__card__method-image' src='/assets/img/braintree-logo.png' alt='Braintree'>

            <div class='sw-braintree-payment-method__card__method-description'>
                <span>{{ $t('configuration.payment.description') }}</span>
            </div>

            <mt-link class='sw-braintree-payment-method__card__method-link' @click='onPaymentMethodDetails'>
                {{ $t("configuration.payment.editDetails") }}
            </mt-link>

            <mt-switch
                :label="$t('configuration.payment.active')"
                :checked='paymentMethod?.active ?? false'
                :disabled='activating'
                @change='onActiveChange'
            />
        </div>

        <div class='sw-braintree-payment-method__card__content'>
            <mt-text-field
                class='sw-braintree-payment-method__card__content__braintreeMerchantId'
                :model-value='shop?.braintreeMerchantId'
                :label="$t('configuration.merchantIdLabel')"
                :placeholder="$t('configuration.merchantIdPlaceholder')"
                :disabled='loading'
                @change='updateShop({ braintreeMerchantId: $event })'
            />

            <mt-text-field
                class='sw-braintree-payment-method__card__content__braintreePublicKey'
                :model-value='shop?.braintreePublicKey'
                :label="$t('configuration.publicKeyLabel')"
                :placeholder="$t('configuration.publicKeyPlaceholder')"
                :disabled='loading'
                @change='updateShop({ braintreePublicKey: $event })'
            />

            <mt-text-field
                class='sw-braintree-payment-method__card__content__braintreePrivateKey'
                :model-value='shop?.braintreePrivateKey'
                :label="$t('configuration.privateKeyLabel')"
                :placeholder="$t('configuration.privateKeyPlaceholder')"
                :disabled='loading'
                @change='updateShop({ braintreePrivateKey: $event })'
            />
        </div>

        <div>
            <mt-switch
                class='sw-braintree-payment-method__card__content__sandboxToggle'
                :label="$t('configuration.payment.isBraintreeSandbox')"
                :disabled='loading'
                :checked='shop?.braintreeSandbox'
                @change='updateShop({ braintreeSandbox: $event })'
            />
        </div>

        <slot />
    </div>
</div>
</template>

<script lang='ts'>
import * as sw from '@shopware-ag/meteor-admin-sdk';
import { defineComponent, type PropType } from 'vue';
import { MtTextField, MtSwitch, MtLink } from '@shopware-ag/meteor-component-library';
import { useStore } from '@/store';
import { mapState } from 'pinia';

const Repository = sw.data.repository<'payment_method'>('payment_method');

export default defineComponent({
    components: { MtSwitch, MtTextField, MtLink },

    emits: ['update:shop', 'update:loading'],

    props: {
        shop: {
            type: Object as PropType<ShopEntity>,
            required: false,
            default: undefined,
        },

        loading: {
            type: Boolean,
            required: true,
        },
    },

    data(): {
        activating: boolean,
    } {
        return {
            activating: false,
        };
    },

    computed: {
        ...mapState(useStore, ['paymentMethod']),
    },

    methods: {
        async onActiveChange(active: boolean) {
            if (!this.paymentMethod)
                return;

            this.activating = true;
            this.$emit('update:loading', true);

            try {
                await Repository.save({ ...this.paymentMethod, active });

                this.paymentMethod.active = active;

                void sw.notification.dispatch({
                    variant: 'success',
                    title: this.$t('notification.success'),
                    message: this.$t(`configuration.payment.${active ? 'enabled' : 'disabled'}`),
                });
            } finally {
                this.$emit('update:loading', false);
                this.activating = false;
            }
        },

        updateShop(update: Partial<ShopEntity>) {
            if (!this.shop)
                throw new Error('Shop config does not exist');

            this.$emit('update:shop', { ...this.shop, ...update });
        },

        onPaymentMethodDetails() {
            if (!this.paymentMethod)
                return;

            void sw.window.routerPush({
                name: 'sw.settings.payment.detail',
                params: { id: this.paymentMethod.id },
            });
        },
    },
});
</script>

<style lang="scss">
.sw-braintree-payment-method {
    &__title {
        margin-top: var(--scale-size-32);
        margin-bottom: var(--scale-size-8);
        font-size: var(--font-size-m);
        line-height: var(--font-line-height-m);
        color: var(--color-text-primary-default);
    }

    &__card {
        padding: var(--scale-size-24);
        border: 1px solid var(--color-border-primary-default);
        border-radius: var(--border-radius-s);
        background: var(--color-elevation-surface-sunken);

        &__method {
            display: grid;
            grid-template-columns: min-content 1fr min-content min-content;
            gap: var(--scale-size-16);
            font-size: var(--font-size-xs);
            align-items: center;

            &-description {
                color: var(--color-text-primary-default);
                font-weight: var(--font-weight-semi-bold);
            }

            &-image {
                height: var(--scale-size-24);
                object-fit: contain;
            }

            &-link {
                white-space: nowrap;
            }
        }

        &__content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: var(--scale-size-16);
            margin: var(--scale-size-16) 0;

            &__braintreeMerchantId {
                grid-column-start: 1;
                grid-column-end: 3;
            
            }

            &__sandboxToggle {
                margin-bottom: var(--scale-size-16);
            }
        }
    }
}
</style>
