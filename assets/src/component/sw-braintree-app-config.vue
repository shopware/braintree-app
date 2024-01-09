<template>
<div class='sw-braintree-payment-method'>
    <div class='sw-braintree-payment-method__title'>
        {{ $tc('configuration.payment.title') }}
    </div>

    <div class='sw-braintree-payment-method__card'>
        <div class='sw-braintree-payment-method__card__method'>
            <img class='sw-braintree-payment-method__card__method-image' src='build/img/braintree-logo.webp' alt='Braintree'>

            <div class='sw-braintree-payment-method__card__method-description'>
                <span>{{ $tc('configuration.payment.description') }}</span>
            </div>

            <sw-switch
                :label="$tc('configuration.payment.active')"
                :checked='$store.getters.paymentMethod?.active ?? false'
                :disabled='activating'
                @change='onActiveChange'
            />
        </div>

        <div class='sw-braintree-payment-method__card__content'>
            <sw-text-field
                :value='shop?.braintreeMerchantId'
                class='sw-braintree-payment-method__card__content__braintreeMerchantId'
                :label="$tc('configuration.merchantIdLabel')"
                :placeholder="$tc('configuration.merchantIdPlaceholder')"
                :disabled='loading'
                @change='updateShop({ braintreeMerchantId: $event })'
            />

            <sw-text-field
                :value='shop?.braintreePublicKey'
                class='sw-braintree-payment-method__card__content__braintreePublicKey'
                :label="$tc('configuration.publicKeyLabel')"
                :placeholder="$tc('configuration.publicKeyPlaceholder')"
                :disabled='loading'
                @change='updateShop({ braintreePublicKey: $event })'
            />

            <sw-text-field
                :value='shop?.braintreePrivateKey'
                class='sw-braintree-payment-method__card__content__braintreePrivateKey'
                :label="$tc('configuration.privateKeyLabel')"
                :placeholder="$tc('configuration.privateKeyPlaceholder')"
                :disabled='loading'
                @change='updateShop({ braintreePrivateKey: $event })'
            />
        </div>

        <div>
            <sw-switch
                :label="$tc('configuration.payment.isBraintreeSandbox')"
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
import * as sw from '@shopware-ag/admin-extension-sdk';
import { defineComponent, type PropType } from 'vue';
import { SwTextField, SwSwitch } from '@shopware-ag/meteor-component-library';

const Repository = sw.data.repository<'payment_method'>('payment_method');

export default defineComponent({
    components: { SwSwitch, SwTextField },

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

    methods: {
        onActiveChange(status: boolean) {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            const paymentMethod = this.$store.getters.paymentMethod as EntitySchema.Entity<'payment_method'>;

            paymentMethod.active = status;

            this.activating = true;
            this.$emit('update:loading', true);

            void Repository.save(paymentMethod).then(() => {
                this.$store.commit('setPaymentMethod', paymentMethod);

                const messageKey = status
                    ? 'configuration.payment.enabled'
                    : 'configuration.payment.disabled';

                void sw.notification.dispatch({
                    variant: 'success',
                    title: this.$tc('notification.success'),
                    message: this.$tc(messageKey),
                });
            }).finally(() => {
                this.$emit('update:loading', false);
                this.activating = false;
            });
        },

        updateShop(update: Partial<ShopEntity>) {
            if (!this.shop)
                throw new Error('Shop config does not exist');

            this.$emit('update:shop', { ...this.shop, ...update });
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
            grid-template-columns: 34px 1fr 80px;
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

            .sw-field--switch {
                margin: 0;
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
