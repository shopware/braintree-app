<template>
<div class='sw-braintree-app-config-page'>
    <mt-banner
        v-if='missingCurrenyMappings'
        variant='attention'
        class='sw-braintree-app-config-page__missing-mappings'
        :closable='false'
        :title='$t("configuration.missingCurrencyMappingTitle")'
    >
        <i18n-t keypath='configuration.missingCurrencyMapping' tag='span'>
            <template #link>
                <mt-external-link @click='onSettingsLinkCicked'>
                    {{ $t("configuration.missingCurrencyMappingLink") }}
                </mt-external-link>
            </template>
        </i18n-t>
    </mt-banner>

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
        <div class='sw-braintree-app-config-page__buttons'>
            <mt-button
                variant='primary'
                size='default'
                :is-loading='saving'
                :disabled='loading || testing'
                @click='saveShopConfig'
            >
                {{ $t('configuration.save') }}
            </mt-button>

            <mt-button
                variant='secondary'
                size='default'
                :is-loading='testing'
                :disabled='loading || saving || !testable'
                @click='onTest'
            >
                {{ $t('configuration.test') }}
            </mt-button>
        </div>
    </sw-braintree-app-config>

    <mt-loader
        v-if='!shop'
    />
</div>
</template>

<script lang='ts'>
import * as sw from '@shopware-ag/meteor-admin-sdk';
import { defineComponent } from 'vue';
import SwBraintreeAppConfig from '@/component/sw-braintree-app-config.vue';
import SwBraintreeAppMerchantDetails from '@/component/sw-braintree-app-merchant-details.vue';
import { MtButton, MtBanner, MtExternalLink, MtLoader } from '@shopware-ag/meteor-component-library';

export default defineComponent({
    name: 'sw-braintree-app-config-page',

    components: {
        SwBraintreeAppMerchantDetails,
        SwBraintreeAppConfig,
        MtButton,
        MtLoader,
        MtBanner,
        MtExternalLink,
    },

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
        missingCurrenyMappings(): boolean {
            return !!this.shop && this.shop.currencyMappings.length === 0;
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
                .then((shop) => {
                    this.shop = shop;
                }).catch((e) => this.$notify.error('fetch_config', e)).finally(() => {
                    this.loading = false;
                });
        },

        async getConnectionStatus(): Promise<void> {
            this.loading = true;

            return this.$api.get<BraintreeConnection>('/config/status')
                .then((connection) => {
                    this.connection = connection;
                })
                .catch((e) => this.$notify.error('fetch_account_status', e))
                .finally(() => {
                    if (!this.saving) this.loading = false;
                });
        },

        async saveShopConfig(): Promise<void> {
            this.saving = true;

            return this.$api.patch<BraintreeConnection>('/entity/shop', this.shop)
                .then(async (connection) => {
                    this.connection = connection;
                    this.notifyConnectionStatus(connection.connectionStatus);
                    await this.getShopConfig();
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
                .then((connection) => this.notifyConnectionStatus(connection.connectionStatus))
                .catch((e) => this.$notify.error('test_config', e))
                .finally(() => {
                    this.loading = false;
                    this.testing = false;
                });
        },

        notifyConnectionStatus(status: string): void {
            if (status === 'active') this.$notify.success('connection');
            else void this.$notify.error('connection');
        },

        onUpdateShop(shop: ShopEntity) {
            this.shop = shop;
        },

        onUpdateLoading(loading: boolean) {
            this.loading = loading;
        },

        async onSettingsLinkCicked() {
            const { modules } = await sw.context.getModuleInformation();
            void sw.window.routerPush({
                name: 'sw.extension.sdk.index',
                params: {
                    id: modules[0].id,
                },
            });
        },
    },
});
</script>

<style lang='scss'>
.sw-braintree-app-config-page {
    &__buttons {
        display: flex;
        gap: 8px;
    }

    &__missing-mappings {
        margin-bottom: 32px;
    }
}
</style>
