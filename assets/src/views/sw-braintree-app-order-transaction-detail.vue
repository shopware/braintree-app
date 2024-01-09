<template>
<div class='sw-braintree-app-order-transaction-detail-page'>
    <sw-loader v-if='loading' class='transaction loading' />
    <div v-else class='transaction'>
        <div v-if='showEmptyState'>
            <div class='empty-state'>
                <sw-icon name='regular-shopping-basket' class='empty-state__icon' />
                <div class='empty-state__title'>
                    {{ $tc('orderTransactionDetail.emptyStateTitle') }}
                </div>
                <div class='empty-state__description'>
                    {{ $tc('orderTransactionDetail.emptyStateDescription') }}
                </div>
            </div>
        </div>
        <div v-else>
            <div class='transaction__header'>
                <div class='transaction__header__logo'>
                    <img
                        class='transaction__header__logo__image'
                        src='build/img/braintree-logo.webp'
                        alt='Braintree'
                    >
                </div>
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
                        {{ parseFloat(transaction.amount) | toCurrency(transaction.currencyIsoCode) }}
                    </div>
                    <div class='transaction__header__detail__date'>
                        {{ transaction.createdAt | toDateTime('short') }}
                    </div>
                </div>
            </div>
            <div class='transaction__body'>
                <div class='transaction__body__transaction-detail'>
                    <div class='transaction__body__transaction-detail__customer-id flex-column'>
                        <span class='transaction__body__title bold'>
                            {{ $tc('orderTransactionDetail.body.customerIdTitle') }}
                        </span>
                        {{ transaction?.customer.id ?? $tc('orderTransactionDetail.body.customerIdEmptyLabel') }}
                    </div>
                    <div class='transaction__body__transaction-detail__amount flex-column'>
                        <span class='transaction__body__title bold'>
                            {{ $tc('orderTransactionDetail.body.amountTitle') }}
                        </span>
                        <div class='transaction__body__transaction-detail__amount__detail flex-column'>
                            <div class='transaction__body__transaction-detail__amount__detail__net flex-column'>
                                <span class='transaction__body__title light'>
                                    {{ $tc('orderTransactionDetail.body.amountNetLabel') }}
                                </span>
                                {{ amountNet | toCurrency(transaction.currencyIsoCode) }}
                            </div>
                            <div class='transaction__body__transaction-detail__amount__detail__shipping flex-column'>
                                <span class='transaction__body__title light'>
                                    {{ $tc('orderTransactionDetail.body.amountShippingLabel') }}
                                </span>
                                {{ parseFloat(transaction.shippingAmount) | toCurrency(transaction.currencyIsoCode) }}
                            </div>
                            <div class='transaction__body__transaction-detail__amount__detail__gross flex-column'>
                                <span class='transaction__body__title light'>
                                    {{ $tc('orderTransactionDetail.body.amountGrossLabel') }}
                                </span>
                                {{ parseFloat(transaction.amount) | toCurrency(transaction.currencyIsoCode) }}
                            </div>
                        </div>
                    </div>
                    <div class='transaction__body__transaction-detail__three-d-s flex-column'>
                        <span class='transaction__body__title bold'>
                            {{ $tc('orderTransactionDetail.body.threeDSTitle') }}
                        </span>
                        <div class='transaction__body__transaction-detail__three-d-s__detail flex-column'>
                            <div class='transaction__body__transaction-detail__three-d-s__detail__liability-possible flex-column'>
                                <span class='transaction__body__title light'>
                                    {{ $tc('orderTransactionDetail.body.threeDSLiabilityPossibleLabel') }}
                                </span>
                                {{ $tc(`orderTransactionDetail.body.threeDSLiabilityPossibleValue.${ transaction.threeDSecureInfo.liabilityShiftPossible }`) }}
                            </div>
                            <div class='transaction__body__transaction-detail__three-d-s__detail__liability-shifted flex-column'>
                                <span class='transaction__body__title light'>
                                    {{ $tc('orderTransactionDetail.body.threeDSLiabilityShiftedLabel') }}
                                </span>
                                {{ $tc(`orderTransactionDetail.body.threeDSLiabilityShiftedValue.${ transaction.threeDSecureInfo.liabilityShifted }`) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class='transaction__body__payment-detail'>
                    <div class='transaction__body__payment-detail__payment flex-column'>
                        <span class='transaction__body__title bold'>
                            {{ $tc('orderTransactionDetail.body.paymentDetailsTitle') }}
                        </span>
                        <div class='transaction__body__payment-detail__payment__detail flex-column'>
                            <div class='transaction__body__payment-detail__payment__detail__status flex-column'>
                                <span class='transaction__body__title light'>
                                    {{ $tc('orderTransactionDetail.body.paymentDetailsStatusLabel') }}
                                </span>
                                <sw-status-indicator :status='statusType' :text='statusText(transaction.status)' />
                            </div>
                            <div class='transaction__body__payment-detail__payment__detail__type flex-column'>
                                <span class='transaction__body__title light'>
                                    {{ $tc('orderTransactionDetail.body.paymentDetailsConclusionTypeLabel') }}
                                </span>
                                {{ $tc('orderTransactionDetail.body.paymentDetailsConclusionTypeValue.immediate') }}
                            </div>
                            <div class='transaction__body__payment-detail__payment__detail__transaction-id flex-column'>
                                <span class='transaction__body__title light'>
                                    {{ $tc('orderTransactionDetail.body.paymentDetailsTransactionIdLabel') }}
                                </span>
                                {{ transaction.id }}
                            </div>
                            <div class='transaction__body__payment-detail__payment__detail__created-at flex-column'>
                                <span class='transaction__body__title light'>
                                    {{ $tc('orderTransactionDetail.body.paymentDetailsCreatedAtLabel') }}
                                </span>
                                {{ transaction.createdAt | toDateTime('short', 'medium') }}
                            </div>
                            <div class='transaction__body__payment-detail__payment__detail__updated-at flex-column'>
                                <span class='transaction__body__title light'>
                                    {{ $tc('orderTransactionDetail.body.paymentDetailsUpdatedAtLabel') }}
                                </span>
                                {{ transaction.updatedAt | toDateTime('short', 'medium') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='transaction__history'>
                <table class='transaction__history__table'>
                    <thead class='transaction__history__table__header'>
                        <tr class='transaction__history__table__header__row'>
                            <th class='transaction__history__table__header__row__cell'>
                                {{ $tc('orderTransactionDetail.history.header.status') }}
                            </th>
                            <th class='transaction__history__table__header__row__cell'>
                                {{ $tc('orderTransactionDetail.history.header.amountCaptured') }}
                            </th>
                            <th class='transaction__history__table__header__row__cell'>
                                {{ $tc('orderTransactionDetail.history.header.timestamp') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class='transaction__history__table__body'>
                        <tr v-for='(history, index) in transaction.statusHistory' :key='index' class='transaction__history__table__body__row'>
                            <td class='transaction__history__table__body__row__cell'>
                                {{ statusText(history.status) }}
                            </td>
                            <td class='transaction__history__table__body__row__cell'>
                                {{ parseFloat(history.amount) | toCurrency(transaction.currencyIsoCode) }}
                            </td>
                            <td class='transaction__history__table__body__row__cell'>
                                {{ history.timestamp.date | toDateTime('short', 'short') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</template>

<script lang='ts'>
import { defineComponent } from 'vue';
import * as sw from '@shopware-ag/admin-extension-sdk';
import { SwLoader, SwIcon } from '@shopware-ag/meteor-component-library';
import SwStatusIndicator from '@/component/base/sw-status-indicator.vue';

const Criteria = sw.data.Classes.Criteria;
const Repository = sw.data.repository<'order_transaction'>('order_transaction');

export default defineComponent({
    name: 'sw-braintree-app-order-transaction-detail',
    components: { SwStatusIndicator, SwLoader, SwIcon },

    data(): {
        loading: boolean,
        transaction: BraintreeTransaction,
    } {
        return {
            loading: true,
            transaction: {} as BraintreeTransaction,
        };
    },

    computed: {
        amountNet(): number {
            return parseFloat(this.transaction.amount) - parseFloat(this.transaction.shippingAmount);
        },

        showEmptyState(): boolean {
            return !this.loading && !Object.keys(this.transaction).length;
        },

        statusType(): StatusIndicatorType {
            switch (this.transaction.status) {
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
    },

    created() {
        void this.loadBraintreeTransaction();
    },

    methods: {
        loadBraintreeTransaction(): void {
            this.loading = true;

            sw.location.stopAutoResizer();
            void sw.location.updateHeight(705);

            void sw.data.subscribe(
                'sw-order-detail-base__order',
                async (response) => {
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
                    const data = response.data as any;

                    const criteria = (new Criteria())
                        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument,@typescript-eslint/no-unsafe-member-access
                        .addFilter(Criteria.equals('orderId', data.id));

                    const result = await Repository.search(criteria);
                    const transactionIds = result?.map((transaction) => transaction.id);

                    void this.$api.post<BraintreeTransaction | null>('/transaction/newest', {
                        transactions: transactionIds,
                    }).then((response) => {
                        if (!response.data)
                            return;

                        this.transaction = response.data;
                    }).finally(() => {
                        sw.location.startAutoResizer();
                        this.loading = false;
                    });
                },
                {
                    selectors: ['id'],
                },
            );
        },

        statusText(braintreeStatus: string): string {
            return this.$tc(`orderTransactionDetail.body.paymentDetailsStatusValue.${ braintreeStatus }`);
        },
    },

});
</script>

<style scoped lang='scss'>
.sw-braintree-app-order-transaction-detail-page {
    .flex-column {
        display: flex;
        flex-direction: column;
    }

    .transaction {
        min-height: 600px;

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            height: 100%;
            padding: 48px;
            text-align: center;

            &__icon {
                font-size: 4rem;
                color: #52667A;

                > svg {
                    width: 400px;
                    height: 400px;
                }
            }

            &__title {
                font-weight: bold;
                font-size: 1.2rem;
            }

            &__description {
                font-size: .9rem;
            }
        }

        &__header {
            display: grid;
            grid-template-columns: 1fr 5fr 1fr;
            margin-bottom: 16px;

            &__logo__image {
                width: 80px;
                border-radius: 4px;
            }

            &__customer,
            &__detail {
                display: flex;
                flex-direction: column;
                justify-content: center;
                gap: 4px;
            }

            &__detail {
                align-items: end;
            }

            &__customer__name,
            &__detail__price {
                font-weight: bold;
                font-size: 1.2rem;
            }
        }

        &__body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-top: 1px solid #d1d9e0;
            padding-top: 28px;
            font-size: .9rem;

            &__transaction-detail,
            &__payment-detail {
                display: flex;
                flex-direction: column;
                gap: 48px;

                &__payment__detail__status {
                    align-self: start;
                }
            }

            &__transaction-detail__amount__detail,
            &__transaction-detail__three-d-s__detail,
            &__payment-detail__payment__detail {
                gap: 28px;
            }

            &__title {
                margin-bottom: 4px;

                &.light {
                    font-weight: lighter;
                }

                &.bold {
                    font-weight: bold;
                    margin-bottom: 24px;
                }
            }
        }

        &__history {
            margin-top: 48px;
            border-top: 1px solid #d1d9e0;

            &__table {
                padding-top: 48px;
                width: 100%;

                &__header__row__cell {
                    font-weight: bold;
                    font-size: 1.2rem;
                    padding: 12px 0;
                    text-align: end;

                    &:first-child {
                        text-align: start;
                    }
                }

                &__body__row__cell {
                    padding: 12px 0;
                    text-align: end;
                    border-top: 1px solid #d1d9e0;

                    &:first-child {
                        text-align: start;
                    }
                }
            }
        }
    }
}
</style>
