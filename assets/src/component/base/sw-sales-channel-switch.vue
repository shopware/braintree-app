<template>
<mt-select
    v-if='!loading'
    class='sw-braintree-sales-channel-switch__select'
    :options='salesChannelOptions'
    :model-value='value ?? undefined'
    :is-loading='isLoading || loading'
    :disabled='disabled'
    :placeholder='$t("settings.salesChannelSwitch.placeholder")'
    @change='onChange'
/>
</template>

<script lang="ts">
import { type PropType, defineComponent } from 'vue';
import * as sw from '@shopware-ag/meteor-admin-sdk';
import { MtSelect } from '@shopware-ag/meteor-component-library';

const Criteria = sw.data.Classes.Criteria;
const Repository = sw.data.repository<'sales_channel'>('sales_channel');

declare type SalesChannelOption = {
    id: string,
    label: string,
    value: string,
};

export default defineComponent({
    name: 'sw-sales-channel-switch',

    components: { MtSelect },

    emits: ['update:value'],

    props: {
        value: {
            type: String as PropType<string | null>,
            required: false,
            default: null,
        },
        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data(): {
        loading: boolean,
        salesChannels: EntitySchema.sales_channel[],
    } {
        return {
            loading: true,
            salesChannels: [],
        };
    },

    computed: {
        salesChannelOptions(): SalesChannelOption[] {
            const salesChannelArray = this.salesChannels.map((salesChannel: EntitySchema.sales_channel) => {
                return {
                    id: salesChannel.id,
                    label: salesChannel.translated?.name || salesChannel.name,
                    value: salesChannel.id,
                };
            });

            return [...salesChannelArray, {
                id: '',
                label: this.$t('settings.allSalesChannels'),
                value: undefined as unknown as string,
            }];
        },
    },

    created() {
        const criteria = new Criteria();
        criteria.addSorting(Criteria.sort('name', 'ASC'));

        void Repository.search(criteria)
            .then((response) => {
                this.salesChannels = (response ?? []);
                this.loading = false;
            });
    },

    methods: {
        onChange(salesChannelId?: string | string[]) {
            // when clearing the selection, an array is returned
            this.$emit('update:value', typeof salesChannelId === 'string' ? salesChannelId : null);
        },
    },
});
</script>

<style lang="scss" scoped>
#app .sw-braintree-sales-channel-switch {
    &__select {
        .sw-select-selection-list__load-more {
            display: none;
        }
    }
}
</style>
