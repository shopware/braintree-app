<template>
<sw-card-view-content class='sw-braintree-app-settings-page'>
    <mt-card class='sw-braintree-app-settings-page__navigation'>
        <template #tabs>
            <mt-tabs
                :items='tabs'
                :default-item='defaultItem'
                @new-item-active='onNewItemActive'
            />
        </template>

        <sw-sales-channel-switch
            class='sw-braintree-app-settings-page__sales-cahnnel-switch'
            :value='salesChannelId'
            @update:value='onUpdateSalesChannel'
        />

        <div class='sw-braintree-app-settings-page__buttons'>
            <mt-link 
                type='internal'
                @click='onConfigLinkCicked'
            >
                {{ $t("configuration.link") }}
            </mt-link>

            <mt-button
                variant='primary'
                size='default'
                @click='onSave'
            >
                {{ $t("settings.saveConfigButton") }}
            </mt-button>
        </div>
    </mt-card>

    <component
        :is='activeTabComponent'
        :sales-channel-id='salesChannelId'
    />
</sw-card-view-content>
</template>


<script lang='ts'>
import { type Component, defineComponent } from 'vue';
import { MtCard, MtButton, MtTabs, MtLink } from '@shopware-ag/meteor-component-library';
import SwBraintreeAppSettingsGeneral from '@/component/sw-braintree-app-settings/sw-braintree-app-settings-general.vue';
import SwBraintreeAppSettingsCurrency from '@/component/sw-braintree-app-settings/sw-braintree-app-settings-currency.vue';
import SwSalesChannelSwitch from '@/component/base/sw-sales-channel-switch.vue';
import SwCardViewContent from '@/component/base/sw-card-view-content.vue';
import { registerSaveHandler, type RegisterSaveHandler } from '@/resources/inject-keys';
import * as sw from '@shopware-ag/meteor-admin-sdk';

type SaveHandler = Parameters<RegisterSaveHandler>[0];

const tabs = {
    swBraintreeAppSettingsGeneral: SwBraintreeAppSettingsGeneral,
    swBraintreeAppSettingsCurrency: SwBraintreeAppSettingsCurrency,
} as const;

export const settingsTabHandler = {
    set(item: keyof typeof tabs): void {
        window.localStorage.setItem('sw-braintree-app-settings-active-tab', item);
    },

    get(): keyof typeof tabs {
        let item = window.localStorage.getItem('sw-braintree-app-settings-active-tab') as null | keyof typeof tabs;

        if (!item || !Object.keys(tabs).includes(item)) 
            item = 'swBraintreeAppSettingsGeneral';

        this.set(item);

        return item;
    },

    clear(): void {
        window.localStorage.removeItem('sw-braintree-app-settings-active-tab');
    },
};

export default defineComponent({
    name: 'sw-braintree-app-settings-page',

    provide() {
        return {
            [registerSaveHandler as symbol]: (handler: SaveHandler) => {
                this.saveHandler.push(handler);
            },
        };
    },

    components: {
        MtCard,
        SwSalesChannelSwitch,
        SwCardViewContent,
        MtButton,
        MtTabs,
        MtLink,
    },

    data(): {
        activeTab: keyof typeof tabs,
        defaultItem: string,
        salesChannelId: string | null,
        tabs: TabItem[],
        saveHandler: SaveHandler[],
    } {
        return {
            activeTab: settingsTabHandler.get(),
            defaultItem: settingsTabHandler.get(),
            salesChannelId: null,
            saveHandler: [],
            tabs: [
                {
                    name: 'swBraintreeAppSettingsGeneral',
                    label: this.$t('settings.tabs.generalLabel'),
                },
                {
                    name: 'swBraintreeAppSettingsCurrency',
                    label: this.$t('settings.tabs.currencyLabel'),
                },
            ],
        };
    },

    computed: {
        activeTabComponent(): Component {
            return tabs[this.activeTab];
        },
    },

    methods: {
        async onNewItemActive(item: keyof typeof tabs): Promise<void> {
            settingsTabHandler.set(item);
            this.activeTab = item;
            this.saveHandler = [];
        },

        onSave(): void {
            this.saveHandler.forEach((handler) => void handler());
        },

        onUpdateSalesChannel(salesChannelId: string): void {
            this.salesChannelId = salesChannelId;
        },

        onConfigLinkCicked(): void {
            void sw.window.routerPush({
                name: 'sw.settings.payment.overview',
            });
        },
    },
});

</script>

<style lang='scss'>
body {
    background: var(--color-background-primary-default);
}

.sw-braintree-app-settings-page {
    &__buttons {
        display: flex;
        justify-content: end;
        margin-top: var(--scale-size-16);
        gap: var(--scale-size-16);

        .mt-link {
            font-size: var(--font-size-xs);
        }
    }
}
</style>
