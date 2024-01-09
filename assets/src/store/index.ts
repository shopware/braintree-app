import Vue from 'vue';
import Vuex from 'vuex';

export declare type ApiContext = {
    systemLanguageId: string,
    languageId: string,
};

export declare type BraintreeState = {
    apiContext: ApiContext | null,
    context: URLSearchParams | null,
    paymentMethod: EntitySchema.Entity<'payment_method'> | null,
};

Vue.use(Vuex);

export default new Vuex.Store<BraintreeState>({
    state: {
        apiContext: null,
        context: null,
        paymentMethod: null,
    } as BraintreeState,

    getters: {
        apiContext: (state: BraintreeState): ApiContext | null => state.apiContext,
        context: (state: BraintreeState): URLSearchParams | null => state.context,
        paymentMethod: (state: BraintreeState): EntitySchema.Entity<'payment_method'> | null => state.paymentMethod,
    },

    mutations: {
        setApiContext(state: BraintreeState, apiContext: ApiContext): void {
            state.apiContext = apiContext;
        },

        setContext(state: BraintreeState, context: URLSearchParams): void {
            state.context = context;
        },

        setPaymentMethod(state: BraintreeState, paymentMethod: EntitySchema.Entity<'payment_method'>): void {
            state.paymentMethod = paymentMethod;
        },
    },
});
