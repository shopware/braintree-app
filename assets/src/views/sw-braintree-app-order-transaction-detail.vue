<template>
<div class='sw-braintree-app-order-transaction-detail-page'>
    <header class='header'>
        <span class='title'>{{ $t('orderTransactionDetail.title') }}</span>
        <mt-link
            v-if='transactionLink'
            type='external'
            class='transaction__link'
            as='a'
            :to='transactionLink'
            target='_blank'
        >
            {{ $t('orderTransactionDetail.transactionLink') }}
        </mt-link>
    </header>

    <div class='divider' />

    <div v-if='loadingTransaction && loadingShop' class='loader'>
        <mt-loader />
    </div>

    <mt-empty-state
        v-else-if='!transaction'
        class='empty-state'
        icon='solid-shopping-basket'
        :headline='$t("orderTransactionDetail.emptyStateTitle")'
        :description='$t("orderTransactionDetail.emptyStateDescription")'
    />

    <div v-else class='transaction'>
        <div class='transaction__header'>
            <img
                class='transaction__header__logo__image'
                src='/assets/img/braintree-logo.png'
                alt='Braintree'
            >
            <div class='transaction__header__customer'>
                <div class='transaction__header__customer__name'>
                    {{ transaction.customer.firstName }} {{ transaction.customer.lastName }}
                </div>
                <div class='transaction__header__customer__email'>
                    {{ transaction.customer.email }}
                </div>
            </div>
            <div class='transaction__header__detail'>
                <div class='transaction__header__detail__price'>
                    {{ $filters.toCurrency(parseFloat(transaction.amount), transaction.currencyIsoCode) }}
                </div>
                <div class='transaction__header__detail__date'>
                    {{ $filters.toDateTime(transaction.createdAt, 'short') }}
                </div>
            </div>
        </div>
        <div class='divider' />
        <div class='transaction__body'>
            <div class='transaction__body__transaction-detail'>
                <div class='transaction__body__transaction-detail__customer-id flex-column'>
                    <span class='transaction__body__title bold'>
                        {{ $t('orderTransactionDetail.body.customerIdTitle') }}
                    </span>
                    {{ transaction?.customer.id ?? $t('orderTransactionDetail.body.customerIdEmptyLabel') }}
                </div>
                <div class='transaction__body__transaction-detail__amount flex-column'>
                    <span class='transaction__body__title bold'>
                        {{ $t('orderTransactionDetail.body.amountTitle') }}
                    </span>
                    <div class='transaction__body__transaction-detail__amount__detail flex-column'>
                        <div class='transaction__body__transaction-detail__amount__detail__net flex-column'>
                            <span class='transaction__body__title light'>
                                {{ $t('orderTransactionDetail.body.amountNetLabel') }}
                            </span>
                            {{ $filters.toCurrency(amountNet, transaction.currencyIsoCode) }}
                        </div>
                        <div class='transaction__body__transaction-detail__amount__detail__shipping flex-column'>
                            <span class='transaction__body__title light'>
                                {{ $t('orderTransactionDetail.body.amountShippingLabel') }}
                            </span>
                            {{ $filters.toCurrency(parseFloat(transaction.shippingAmount), transaction.currencyIsoCode) }}
                        </div>
                        <div class='transaction__body__transaction-detail__amount__detail__gross flex-column'>
                            <span class='transaction__body__title light'>
                                {{ $t('orderTransactionDetail.body.amountGrossLabel') }}
                            </span>
                            {{ $filters.toCurrency(parseFloat(transaction.amount), transaction.currencyIsoCode) }}
                        </div>
                    </div>
                </div>
                <div class='transaction__body__transaction-detail__three-d-s flex-column'>
                    <span class='transaction__body__title bold'>
                        {{ $t('orderTransactionDetail.body.threeDSTitle') }}
                    </span>
                    <div class='transaction__body__transaction-detail__three-d-s__detail flex-column'>
                        <div class='transaction__body__transaction-detail__three-d-s__detail__liability-possible flex-column'>
                            <span class='transaction__body__title light'>
                                {{ $t('orderTransactionDetail.body.threeDSLiabilityPossibleLabel') }}
                            </span>
                            {{ $t(`orderTransactionDetail.body.threeDSLiabilityPossibleValue.${ transaction.threeDSecureInfo.liabilityShiftPossible }`) }}
                        </div>
                        <div class='transaction__body__transaction-detail__three-d-s__detail__liability-shifted flex-column'>
                            <span class='transaction__body__title light'>
                                {{ $t('orderTransactionDetail.body.threeDSLiabilityShiftedLabel') }}
                            </span>
                            {{ $t(`orderTransactionDetail.body.threeDSLiabilityShiftedValue.${ transaction.threeDSecureInfo.liabilityShifted }`) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class='transaction__body__payment-detail'>
                <div class='transaction__body__payment-detail__payment flex-column'>
                    <span class='transaction__body__title bold'>
                        {{ $t('orderTransactionDetail.body.paymentDetailsTitle') }}
                    </span>
                    <div class='transaction__body__payment-detail__payment__detail flex-column'>
                        <div class='transaction__body__payment-detail__payment__detail__status flex-column'>
                            <span class='transaction__body__title light'>
                                {{ $t('orderTransactionDetail.body.paymentDetailsStatusLabel') }}
                            </span>
                            <sw-status-indicator :status='statusType' :text='statusText(transaction.status)' />
                        </div>
                        <div class='transaction__body__payment-detail__payment__detail__type flex-column'>
                            <span class='transaction__body__title light'>
                                {{ $t('orderTransactionDetail.body.paymentDetailsConclusionTypeLabel') }}
                            </span>
                            {{ $t('orderTransactionDetail.body.paymentDetailsConclusionTypeValue.immediate') }}
                        </div>
                        <div class='transaction__body__payment-detail__payment__detail__transaction-id flex-column'>
                            <span class='transaction__body__title light'>
                                {{ $t('orderTransactionDetail.body.paymentDetailsTransactionIdLabel') }}
                            </span>
                            {{ transaction.id }}
                        </div>
                        <div class='transaction__body__payment-detail__payment__detail__created-at flex-column'>
                            <span class='transaction__body__title light'>
                                {{ $t('orderTransactionDetail.body.paymentDetailsCreatedAtLabel') }}
                            </span>
                            {{ $filters.toDateTime(transaction.createdAt, 'short', 'medium') }}
                        </div>
                        <div class='transaction__body__payment-detail__payment__detail__updated-at flex-column'>
                            <span class='transaction__body__title light'>
                                {{ $t('orderTransactionDetail.body.paymentDetailsUpdatedAtLabel') }}
                            </span>
                            {{ $filters.toDateTime(transaction.updatedAt, 'short', 'medium') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='divider' />
        <div class='transaction__history'>
            <table class='transaction__history__table'>
                <thead class='transaction__history__table__header'>
                    <tr class='transaction__history__table__header__row'>
                        <th class='transaction__history__table__header__row__cell'>
                            {{ $t('orderTransactionDetail.history.header.status') }}
                        </th>
                        <th class='transaction__history__table__header__row__cell'>
                            {{ $t('orderTransactionDetail.history.header.amountCaptured') }}
                        </th>
                        <th class='transaction__history__table__header__row__cell'>
                            {{ $t('orderTransactionDetail.history.header.timestamp') }}
                        </th>
                    </tr>
                </thead>
                <tbody class='transaction__history__table__body'>
                    <tr
                        v-for='(history, index) in transaction.statusHistory'
                        :key='index'
                        class='transaction__history__table__body__row'
                    >
                        <td class='transaction__history__table__body__row__cell'>
                            {{ statusText(history.status) }}
                        </td>
                        <td class='transaction__history__table__body__row__cell'>
                            {{ $filters.toCurrency(parseFloat(history.amount), transaction.currencyIsoCode) }}
                        </td>
                        <td class='transaction__history__table__body__row__cell'>
                            {{ $filters.toDateTime(history.timestamp.date, 'short', 'short') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</template>

<script lang='ts'>
import { defineComponent } from 'vue';
import * as sw from '@shopware-ag/meteor-admin-sdk';
import { MtLoader, MtEmptyState, MtLink } from '@shopware-ag/meteor-component-library';
import SwStatusIndicator from '@/component/base/sw-status-indicator.vue';

const Criteria = sw.data.Classes.Criteria;
const Repository = sw.data.repository<'order_transaction'>('order_transaction');

export default defineComponent({
    name: 'sw-braintree-app-order-transaction-detail',
    components: { SwStatusIndicator, MtLoader, MtEmptyState, MtLink },

    data(): {
        loadingTransaction: boolean,
        loadingShop: boolean,
        transaction: BraintreeTransaction | null,
        shop: ShopEntity | null,
    } {
        return {
            loadingTransaction: true,
            loadingShop: true,
            transaction: null,
            shop: null,
        };
    },

    computed: {
        amountNet(): number {
            return parseFloat(this.transaction?.amount ?? '') - parseFloat(this.transaction?.shippingAmount ?? '');
        },

        statusType(): StatusIndicatorType {
            switch (this.transaction?.status) {
                case 'authorization_expired':
                case 'settlement_declined':
                case 'failed':
                case 'gateway_rejected':
                case 'processor_declined':
                    return 'danger';

                case 'authorized':
                case 'authorizing':
                case 'settlement_pending':
                case 'submitted_for_settlement':
                case 'settling':
                    return 'warning';

                case 'settled':
                    return 'success';

                default:
                    return undefined;
            }
        },

        transactionLink(): string {
            if (this.loadingTransaction || this.loadingShop || !this.transaction || !this.shop)
                return '';

            return `https://${this.shop?.braintreeSandbox ? 'sandbox.' : ''}braintreegateway.com/merchants/${this.shop?.braintreeMerchantId}/transactions/${this.transaction?.id}`;
        },
    },

    created() {
        this.loadBraintreeTransaction();
        this.getShopConfig();
    },

    methods: {
        async getShopConfig(): Promise<void> {
            this.loadingShop = true;

            return this.$api.get<ShopEntity>('/entity/shop')
                .then((shop) => { this.shop = shop; })
                .catch((e) => this.$notify.error('fetch_config', e))
                .finally(() => { this.loadingShop = false; });
        },

        loadBraintreeTransaction() {
            void sw.data.subscribe(
                'sw-order-detail-base__order',
                async ({ data }) => {
                    this.loadingTransaction = true;
                    this.transaction = null;

                    const criteria = (new Criteria())
                        .addFilter(Criteria.equals('orderId', (data as { id: string }).id));

                    const result = await Repository.search(criteria);
                    const transactionIds = result?.map((transaction) => transaction.id) || null;

                    void this.$api.post<BraintreeTransaction | null>('/transaction/newest', {
                        transactions: transactionIds,
                    }).then((transaction) => {
                        if (!transaction)
                            return;

                        this.transaction = transaction;
                    }).finally(() => {
                        this.loadingTransaction = false;
                    });
                },
                {
                    selectors: ['id'],
                },
            );
        },

        statusText(braintreeStatus: string): string {
            return this.$t(`orderTransactionDetail.body.paymentDetailsStatusValue.${ braintreeStatus }`);
        },
    },

});
</script>

<style scoped lang='scss'>
.sw-braintree-app-order-transaction-detail-page {
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: var(--font-size-s);
        line-height: var(--font-line-height-s);

        .title {
            font-size: var(--font-size-m);
            line-height: var(--font-line-height-m);
            font-weight: var(--font-weight-semibold);
            color: var(--color-text-primary-default);
        }
    }

    background: var(--color-elevation-surface-default);

    .flex-column {
        display: flex;
        flex-direction: column;
    }

    .loader {
        height: 80px;
        position: relative;

        .mt-loader {
            background: var(--color-elevation-surface-default);
        }
    }

    .empty-state {
        align-items: center;
        justify-content: center;
    }

    .divider {
        border-top: 1px solid var(--color-border-primary-default);
        height: 0;
        margin: var(--scale-size-24) 0;
    }

    .transaction {
        display: grid;
        grid-template-rows: auto 1fr auto;
        font-size: var(--font-size-xs);
        line-height: var(--font-line-height-xs);
        min-height: 600px;

        & > * {
            padding: 0 var(--scale-size-8);
        }

        &__header {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: var(--scale-size-32);
            font-size: var(--font-size-s);
            line-height: var(--font-line-height-s);

            &__logo__image {
                width: var(--scale-size-80);
                border-radius: var(--border-radius-s);
            }

            &__customer,
            &__detail {
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            &__detail {
                align-items: end;
            }

            &__customer__name,
            &__detail__price {
                font-weight: var(--font-weight-bold);
                font-size: var(--font-size-m);
                line-height: var(--font-line-height-m);
            }
        }

        &__body {
            display: grid;
            grid-template-columns: 1fr 1fr;

            &__transaction-detail,
            &__payment-detail {
                display: flex;
                flex-direction: column;
                gap: var(--scale-size-48);

                &__payment__detail__status {
                    align-self: start;
                }
            }

            &__transaction-detail__amount__detail,
            &__transaction-detail__three-d-s__detail,
            &__payment-detail__payment__detail {
                gap: var(--scale-size-28);
            }

            &__title {
                &.light {
                    color: var(--color-text-primary-disabled);
                }

                &.bold {
                    font-weight: var(--font-weight-bold);
                    margin-bottom: var(--scale-size-24);
                }
            }
        }

        &__history {
            &__table {
                width: 100%;

                &__header__row__cell {
                    font-weight: bold;
                    font-size: var(--font-size-s);
                    line-height: var(--font-line-height-s);
                    padding-bottom: var(--scale-size-12);
                    text-align: end;

                    &:first-child {
                        text-align: start;
                    }
                }

                &__body__row__cell {
                    padding: var(--scale-size-12) 0;
                    text-align: end;
                    border-top: 1px solid var(--color-border-primary-default);

                    &:first-child {
                        text-align: start;
                    }
                }
            }
        }
    }
}
</style>
