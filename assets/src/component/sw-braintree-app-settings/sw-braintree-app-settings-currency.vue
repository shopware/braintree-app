<template>
<div>
    <sw-banner
        v-if='missingAccount'
        variant='attention'
        class='sw-braintree-app-settings-currency__missing-account'
        :closable='false'
        :title='$tc("settings.currency.missingAccountTitle")'
    >
        <i18n path='settings.currency.missingAccount' tag='span'>
            <template #link>
                <sw-external-link @click='onPaymentMethodOverview'>
                    {{ $tc("settings.currency.missingAccountLink") }}
                </sw-external-link>
            </template>
        </i18n>
    </sw-banner>

    <sw-card
        :title='$tc("settings.currency.table.title")'
        :is-loading='loading'
    >
        <template #grid>
            <sw-braintree-app-table
                v-if='!loading'
                :columns='columns'
                :items='currencies'
            >
                <template #cell-currency='{ item }'>
                    {{ item.translated.name }} ({{ item.isoCode }})
                </template>

                <template #cell-merchantAccount='{ item }'>
                    <sw-braintree-app-currency-mapping-select
                        :item='item'
                        :mappings='mergedMappings'
                        :merchants='filteredMerchantAccounts(item.isoCode)'
                        :loading='loadingInputs'
                        :inheritance='!!salesChannelId'
                        :disabled='missingAccount'
                        @update:mapping='onUpdateCurrencyMapping'
                        @delete:mapping='onDeleteCurrencyMapping'
                        @remove:inheritance='onRemoveInheritance(item.id, item.isoCode)'
                        @restore:inheritance='onRestoreInheritance(item.id)'
                    />
                </template>
            </sw-braintree-app-table>

            <sw-braintree-app-table
                v-else
                class='sw-braintree-app-settings-currency__loading-table'
                :columns='columns'
                :items='[{},{},{}]'
            />
        </template>
    </sw-card>
</div>
</template>

<script lang='ts'>
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import SwBraintreeAppTable from '../base/sw-braintree-app-table.vue';
import SwBraintreeAppCurrencyMappingSelect from './sw-braintree-app-settings-currency-mapping-select.vue';
import { SwCard, SwBanner, SwExternalLink } from '@shopware-ag/meteor-component-library';
import * as sw from '@shopware-ag/meteor-admin-sdk';
import { DefaultCurrencyMappingEntity } from '@/resources/entities';
import { registerSaveHandler } from '@/resources/inject-keys';
import BraintreeCurrencies from '@/resources/braintree-currencies';

type Currencies = EntitySchema.EntityCollection<'currency'>;

const Criteria = sw.data.Classes.Criteria;
const currencyRepository = sw.data.repository<'currency'>('currency');

export default defineComponent({
    name: 'sw-braintree-app-settings-currency',

    inject: {
        registerSaveHandler: {
            from: registerSaveHandler,
            default: () => {},
        },
    },

    components: {
        SwBraintreeAppTable,
        SwCard,
        SwBraintreeAppCurrencyMappingSelect,
        SwBanner,
        SwExternalLink,
    },

    props: {
        salesChannelId: {
            type: String as PropType<string | null>,
            required: false,
            default: null,
        },
    },

    data(): {
        missingAccount: boolean,
        hasChanges: boolean,
        loading: boolean,
        loadingInputs: boolean,
        columns: BrainTableColumn[],
        merchantAccounts: MerchantAccount[],
        mappings: CurrencyMappingEntity[],
        deletedMappings: CurrencyMappingEntity[],
        currencies: Currencies,
    } {
        return {
            missingAccount: false,
            hasChanges: false,
            loading: true,
            loadingInputs: true,
            mappings: [],
            deletedMappings: [],
            merchantAccounts: [],
            currencies: [] as unknown as Currencies,
            columns: [{
                property: 'currency',
                label: this.$tc('settings.currency.table.columns.shopwareCurrencyLabel'),
            }, {
                property: 'merchantAccount',
                label: this.$tc('settings.currency.table.columns.braintreeMerchantAccount'),
            }],
        };
    },

    computed: {
        channelId(): string {
            return String(this.salesChannelId);
        },

        filteredMappings(): CurrencyMappingEntity[] {
            return this.mappings.filter((m) => m.salesChannelId === this.salesChannelId);
        },

        filteredDeletedMappings(): CurrencyMappingEntity[] {
            return this.deletedMappings.filter((m) => m.salesChannelId === this.salesChannelId);
        },

        mergedMappings(): CurrencyMappingEntity[] {
            return this.filteredMappings
                .filter((m) => !this.filteredDeletedMappings.find((d) => d.id === m.id && d.currencyId === m.currencyId));
        },
    },

    watch: {
        salesChannelId(salesChannelId: string | null) {
            void this.getCurrencyMappings(salesChannelId).then(() => this.loadingInputs = false);
        },
    },

    created() {
        // @ts-expect-error - TODO: Fix this
        // eslint-disable-next-line @typescript-eslint/no-unsafe-call
        this.registerSaveHandler(this.saveCurrencyMappings.bind(this));
        void this.getShopwareCurrencies();

        const promises = [this.getMerchantAccounts(), this.getCurrencyMappings()];
        void Promise.all(promises).then(() => this.loadingInputs = false);
    },

    beforeDestroy() {
        if (!this.hasChanges) return;
        void this.saveCurrencyMappings();
    },

    methods: {
        filteredMerchantAccounts(isoCode: string): MerchantAccount[] {
            return this.merchantAccounts.filter((m) => m.currencyIsoCode === isoCode);
        },

        async getMerchantAccounts(): Promise<void> {
            this.loadingInputs = true;

            return this.$api.get<MerchantAccount[]>('/braintree/merchant_accounts')
                .then((response) => {this.merchantAccounts = response.data ?? [];})
                .catch(() => {this.missingAccount = true;});
        },

        async getCurrencyMappings(salesChannelId: string | null = null): Promise<void> {
            if (this.mappings.find((m) => m.salesChannelId === salesChannelId)) return;

            this.loadingInputs = true;

            return this.$api
                .get<CurrencyMappingEntity[]>(`/entity/by-sales-channel/currency_mapping/${String(salesChannelId)}`)
                .then((response) => void this.mappings.push(...response.data ?? []))
                .catch((e) => this.$notify.error('fetch_settings', e));
        },

        async saveCurrencyMappings(): Promise<void> {
            this.loadingInputs = true;

            const deleted = Object.values(this.deletedMappings)
                .map((d) => d.id)
                .unique(true);

            const mappings = this.mappings
                .filter((m) => !this.deletedMappings.find((d) => d.id === m.id && d.currencyId === m.currencyId));

            return this.$api.patch('/entity/currency_mapping', { upsert: mappings, deleted })
                .then(() => {
                    this.$notify.success('save_settings');

                    this.mappings = [];
                    this.deletedMappings = [];
                    return this.getCurrencyMappings(this.salesChannelId);
                }).catch((e) => this.$notify.error('save_settings', e))
                .finally(() => {
                    this.loadingInputs = false;
                });
        },

        async getShopwareCurrencies(): Promise<void> {
            this.loading = true;

            const criteria = (new Criteria())
                .setTotalCountMode(0)
                .addFilter(Criteria.equalsAny('isoCode', BraintreeCurrencies))
                .addSorting(Criteria.sort('name'));

            return currencyRepository.search(criteria)
                .then((response) => {
                    this.currencies = response ?? ([] as unknown as Currencies);
                    this.loading = false;
                }).catch((e) => this.$notify.error('fetch_currencies', e));
        },

        onUpdateCurrencyMapping(mapping: CurrencyMappingEntity, merchantAccountId: string | null) {
            this.hasChanges = true;

            const idx = this.mappings.findIndex((m) => m.salesChannelId === this.salesChannelId && m.id === mapping.id && m.currencyId === mapping.currencyId);
            const dIdx = this.deletedMappings.findIndex((m) => m.salesChannelId === this.salesChannelId && m.id === mapping.id && m.currencyId === mapping.currencyId);

            if (dIdx >= 0) {
                if (idx > 1) this.mappings[idx].merchantAccountId = merchantAccountId;
                this.deletedMappings.splice(dIdx, 1);

                return;
            }

            if (idx < 0) this.mappings.push(mapping);
            else mapping = this.mappings[idx];

            mapping.merchantAccountId = merchantAccountId;
            mapping.salesChannelId = this.salesChannelId;
        },

        onDeleteCurrencyMapping(mapping: CurrencyMappingEntity) {
            this.hasChanges = true;

            const idx = this.mappings.findIndex((m) => m.salesChannelId === this.salesChannelId && m.id === mapping.id && m.currencyId === mapping.currencyId);
            const dIdx = this.deletedMappings.findIndex((m) => m.salesChannelId === this.salesChannelId && m.id === mapping.id && m.currencyId === mapping.currencyId);

            if (dIdx >= 0) {
                if (!mapping.id) this.deletedMappings.splice(dIdx, 1);

                return;
            }

            if (mapping.id) this.deletedMappings.push({ ...mapping });
            else if (this.salesChannelId) this.mappings[idx].merchantAccountId = null;
            else this.mappings.splice(idx, 1);

        },

        onRemoveInheritance(currencyId: string, currencyIso: string) {
            this.hasChanges = true;

            const idx = this.mappings.findIndex((m) => m.salesChannelId === this.salesChannelId && m.currencyId === currencyId);
            const dIdx = this.deletedMappings.findIndex((m) => m.salesChannelId === this.salesChannelId && m.currencyId === currencyId);

            if (dIdx >= 0) this.deletedMappings.splice(dIdx, 1);
            if (idx > -1) return;

            this.mappings.push({
                ...this.mappings.find((m) => m.salesChannelId === null && m.currencyId === currencyId) ?? DefaultCurrencyMappingEntity(),
                id: null,
                salesChannelId: this.salesChannelId,
                currencyIso,
                currencyId,
            });
        },

        onRestoreInheritance(currencyId: string) {
            this.hasChanges = true;

            const idx = this.mappings.findIndex((m) => m.salesChannelId === this.salesChannelId && m.currencyId === currencyId);
            const dIdx = this.deletedMappings.findIndex((m) => m.salesChannelId === this.salesChannelId && m.currencyId === currencyId);

            if (dIdx >= 0 || idx < 0) return;

            if (this.mappings[idx].id) this.deletedMappings.push({ ...this.mappings[idx] });
            else this.mappings.splice(idx, 1);
        },

        onPaymentMethodOverview() {
            void sw.window.routerPush({
                name: 'sw.settings.payment.overview',
            });
        },
    },
});
</script>

<style lang='scss'>
.sw-braintree-app-settings-currency {
    &__loading-table {
        .sw-braintree-app-table__columns {
            height: 70px;
        }
    }

    &__merchant-account-select {
        display: flex;
        align-items: center;
        gap: 4px;

        .sw-field__label {
            width: auto;
            margin: 0;

            label {
                display: none;
            }
        }

        .sw-block-field__block {
            width: 100%;
        }

        .sw-field__hint {
            display: none
        }
    }

    &__merchant-account-select:not(.is-inheritance) {
        .sw-field__label {
            display: none;
        }
    }

    &__missing-account {
        max-width: 960px;

        .sw-external-link {
            font-size: 16px;
        }
    }
}
</style>
