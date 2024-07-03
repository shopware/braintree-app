import { defineStore } from 'pinia';

export type State = {
    paymentMethod: EntitySchema.Entity<'payment_method'> | null,
};

export const useStore = defineStore('braintree', {
    state: (): State => ({
        paymentMethod: null,
    }),
    actions: {
        setPaymentMethod(paymentMethod: EntitySchema.Entity<'payment_method'>): void {
            this.paymentMethod = paymentMethod;
        },
    },
});
