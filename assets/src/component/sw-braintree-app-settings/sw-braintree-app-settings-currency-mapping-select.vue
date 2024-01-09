<template>
<sw-select
    :value='mapping?.merchantAccountId'
    :options='merchants'
    :enable-multi-selection='false'
    :hide-clearable-button='false'
    :small='true'
    :is-loading='loading'
    :is-inheritance-field='inheritance'
    :is-inherited='inheritance && !mapping'
    :disabled='(inheritance && !mapping) || loading || disabled'
    label=''
    label-property='id'
    value-property='id'
    class='sw-braintree-app-settings-currency__merchant-account-select'
    :class='inheritance ? "is-inheritance" : ""'
    @change='onUpdateMerchantAccount($event)'
    @paginate='$emit("load:merchants", item.isoCode)'
    @click.native='$emit("load:merchants", item.isoCode)'
    @inheritance-remove='$emit("remove:inheritance")'
    @inheritance-restore='$emit("restore:inheritance")'
/>
</template>

<script lang='ts'>
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { SwSelect } from '@shopware-ag/meteor-component-library';
import { DefaultCurrencyMappingEntity } from '@/resources/entities';

export default defineComponent({
    name: 'sw-braintree-app-settings-currency',

    components: {
        SwSelect,
    },

    emits: [
        'load:merchants',
        'update:mapping',
        'delete:mapping',
        'remove:inheritance',
        'restore:inheritance',
    ],

    props: {
        item: {
            type: Object as PropType<EntitySchema.Entity<'currency'>>,
            required: true,
        },
        mappings: {
            type: Array as PropType<CurrencyMappingEntity[]>,
            required: false,
            default: () => [],
        },
        inheritance: {
            type: Boolean,
            required: false,
            default: false,
        },
        merchants: {
            type: Array as PropType<MerchantAccount[]>,
            required: true,
        },
        loading: {
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

    data() {
        return {
            notInherited: false,
        };
    },

    computed: {
        mapping() {
            return this.mappings.find((mapping) => mapping.currencyId === this.item.id);
        },
    },

    methods: {
        onUpdateMerchantAccount(merchantAccount: string) {
            if (Array.isArray(merchantAccount)) {
                this.$emit('delete:mapping', this.mapping);
                return;
            }

            const mapping = this.mapping ?? DefaultCurrencyMappingEntity();
            mapping.currencyIso = this.merchants.find((m) => m.id === merchantAccount)?.currencyIsoCode ?? this.item.isoCode;
            mapping.currencyId = this.item.id;

            this.$emit('update:mapping', mapping, merchantAccount);
        },
    },
});
</script>
