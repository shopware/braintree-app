<template>
<div class='sw-braintree-app-config-page'>
    <sw-braintree-app-merchant-details
        :connection='connection'
        :loading='!connection'
        @disconnect='onDisconnect'
    />

    <sw-braintree-app-config
        :shop='shop'
        :loading='!shop || testing || saving'
        @update:shop='onUpdateShop'
        @update:loading='onUpdateLoading'
    >
        <sw-button
            variant='primary'
            size='default'
            :is-loading='saving'
            :disabled='loading || testing'
            @click='saveShopConfig'
        >
            {{ $tc('configuration.save') }}
        </sw-button>

        <sw-button
            variant='secondary'
            size='default'
            :is-loading='testing'
            :disabled='loading || saving || !testable'
            @click='onTest'
        >
            {{ $tc('configuration.test') }}
        </sw-button>
    </sw-braintree-app-config>

    <sw-loader
        v-if='!shop'
    />
</div>
</template>

<script lang='ts'>
import * as sw from '@shopware-ag/meteor-admin-sdk';
import { defineComponent } from 'vue';
import SwBraintreeAppConfig from '@/component/sw-braintree-app-config.vue';
import SwBraintreeAppMerchantDetails from '@/component/sw-braintree-app-merchant-details.vue';
import { SwButton, SwLoader } from '@shopware-ag/meteor-component-library';

export default defineComponent({
    name: 'sw-braintree-app-config-page',

    components: { SwBraintreeAppMerchantDetails, SwBraintreeAppConfig, SwButton, SwLoader },

    data(): {
        shop?: ShopEntity,
        loading: boolean,
        saving: boolean,
        testing: boolean,
        connection?: BraintreeConnection,
    } {
        return {
            shop: undefined,
            loading: true,
            saving: false,
            testing: false,
            connection: undefined,
        };
    },

    computed: {
        testable(): boolean {
            return !!this.shop?.braintreeMerchantId && !!this.shop?.braintreePublicKey && !!this.shop?.braintreePrivateKey;
        },
    },

    created(): void {
        if (!sw.location.is('swag-braintree-app-payment-overview-position-before'))
            return;

        void this.getConnectionStatus();
        void this.getShopConfig().finally(() => {
            sw.location.startAutoResizer();
        });
    },

    methods: {
        async getShopConfig(): Promise<void> {
            this.loading = true;

            return this.$api.get<ShopEntity>('/entity/shop')
                .then((response) => {
                    this.shop = response.data;
                }).catch((e) => this.$notify.error('fetch_config', e)).finally(() => {
                    this.loading = false;
                });
        },

        async getConnectionStatus(): Promise<void> {
            this.loading = true;

            return this.$api.get<BraintreeConnection>('/config/status')
                .then((response) => {
                    this.connection = response.data;
                })
                .catch((e) => this.$notify.error('fetch_account_status', e))
                .finally(() => {
                    if (!this.saving) this.loading = false;
                });
        },

        async saveShopConfig(): Promise<void> {
            this.saving = true;

            return this.$api.patch<BraintreeConnection>('/entity/shop', this.shop)
                .then((response) => {
                    this.connection = response.data;
                    this.notifyConnectionStatus(response.data.connectionStatus);
                })
                .catch((e) => this.$notify.error('save_config', e))
                .finally(() => this.saving = false);
        },

        async onDisconnect(): Promise<void> {
            this.loading = true;

            return this.$api.delete('/config')
                .then(() => {
                    this.connection = {
                        merchantAccount: null,
                        connectionStatus: 'disconnected',
                    };
                })
                .catch((e) => this.$notify.error('reset_config', e))
                .finally(() => {
                    void this.getShopConfig();
                });
        },

        onTest() {
            this.loading = true;
            this.testing = true;

            void this.$api.post<BraintreeConnection>('/config/test', this.shop)
                .then((response) => this.notifyConnectionStatus(response.data.connectionStatus))
                .catch((e) => this.$notify.error('test_config', e))
                .finally(() => {
                    this.loading = false;
                    this.testing = false;
                });
        },

        notifyConnectionStatus(status: string): void {
            if (status === 'active') this.$notify.success('connection');
            else this.$notify.error('connection');
        },

        onUpdateShop(shop: ShopEntity) {
            this.shop = shop;
        },

        onUpdateLoading(loading: boolean) {
            this.loading = loading;
        },
    },
});
</script>
