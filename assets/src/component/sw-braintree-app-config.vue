<template>
<div class='sw-braintree-payment-method'>
    <div class='sw-braintree-payment-method__title'>
        {{ $t('configuration.payment.title') }}
    </div>

    <div class='sw-braintree-payment-method__card'>
        <div class='sw-braintree-payment-method__card__method'>
            <img class='sw-braintree-payment-method__card__method-image' src='/assets/img/braintree-logo.webp' alt='Braintree'>

            <div class='sw-braintree-payment-method__card__method-description'>
                <span>{{ $t('configuration.payment.description') }}</span>
            </div>

            <span
                class='sw-braintree-payment-method__card__method-link'
                @click='onPaymentMethodDetails'
            >
                {{ $t("configuration.payment.editDetails") }}
            </span>

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
import { MtTextField, MtSwitch } from '@shopware-ag/meteor-component-library';
import { useStore } from '@/store';
import { mapState } from 'pinia';

const Repository = sw.data.repository<'payment_method'>('payment_method');

export default defineComponent({
    components: { MtSwitch, MtTextField },

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
        margin-top: 32px;
        margin-bottom: 8px;
        font-weight: 400;
        font-size: 18px;
        line-height: 23px;
        color: #52667A;
    }

    &__card {
        padding: 21px 24px;
        border: 1px solid #D1D9E0;
        border-radius: 4px;
        background: #F9FAFB;

        &__method {
            display: grid;
            grid-template-columns: 34px 1fr min-content 80px;
            gap: 16px;
            font-size: 14px;
            align-items: center;

            &-description {
                color: #52667A;
                font-weight: 600;
            }

            &-image {
                height: 24px;
                object-fit: contain;
            }

            &-link {
                padding-bottom: 4px;
                white-space: nowrap;
                color: #189eff;
                text-decoration: underline;
                cursor: pointer;
            }
        }

        &__content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 8px;
            margin: 12px 0;

            &__braintreeMerchantId {
                grid-column-start: 1;
                grid-column-end: 3;
            }
        }
    }
}
</style>
