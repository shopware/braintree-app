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

        <div class='sw-braintree-app-settings-page__save'>
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
import { MtCard, MtButton, MtTabs } from '@shopware-ag/meteor-component-library';
import SwBraintreeAppSettingsGeneral from '@/component/sw-braintree-app-settings/sw-braintree-app-settings-general.vue';
import SwBraintreeAppSettingsCurrency from '@/component/sw-braintree-app-settings/sw-braintree-app-settings-currency.vue';
import SwSalesChannelSwitch from '@/component/base/sw-sales-channel-switch.vue';
import SwCardViewContent from '@/component/base/sw-card-view-content.vue';
import { registerSaveHandler, type RegisterSaveHandler } from '@/resources/inject-keys';

type SaveHandler = Parameters<RegisterSaveHandler>[0];

const tabs: Record<string, Component> = {
    swBraintreeAppSettingsGeneral: SwBraintreeAppSettingsGeneral,
    swBraintreeAppSettingsCurrency: SwBraintreeAppSettingsCurrency,
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
    },

    data(): {
        activeTab: string,
        defaultItem: string,
        salesChannelId: string | null,
        tabs: TabItem[],
        saveHandler: SaveHandler[],
    } {
        return {
            activeTab: 'swBraintreeAppSettingsGeneral',
            defaultItem: 'swBraintreeAppSettingsGeneral',
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
        onNewItemActive(item: string): void {
            this.activeTab = item;
            this.saveHandler = [];
        },

        onSave(): void {
            this.saveHandler.forEach((handler) => void handler());
        },

        onUpdateSalesChannel(salesChannelId: string): void {
            this.salesChannelId = salesChannelId;
        },
    },
});

</script>

<style lang='scss'>
body {
    background: #f9fafb;
}

.sw-braintree-app-settings-page {
    &__navigation.sw-card {
        .sw-field {
            margin-top: 40px;
            margin-bottom: 0;
        }

        &:not(.sw-card--hero) {
            box-shadow: none;
        }

        .sw-card__content {
            padding: 0;
            background: transparent;
        }

        .sw-tabs {
            padding: 0;
        }
    }

    &__save {
        display: flex;
        justify-content: end;
        margin-top: 16px;
    }
}
</style>
