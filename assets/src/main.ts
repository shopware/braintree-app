import * as sw from '@shopware-ag/meteor-admin-sdk';
import store from '@/store';
import Vue from 'vue';
import { DeviceHelperPlugin, TooltipDirective } from '@shopware-ag/meteor-component-library';
import VueI18n from 'vue-i18n';
import messages from '@/i18n';
import { Api } from '@/service/api';
import { addLocations } from '@/location';
import type { Entity } from '@shopware-ag/meteor-admin-sdk/es/data/_internals/Entity';
import { Notify } from './service/notify';

const Criteria = sw.data.Classes.Criteria;
const Repository = sw.data.repository<'payment_method'>('payment_method');
const criteria = new Criteria();

criteria.addFilter(
    Criteria.equals('handlerIdentifier', 'app\\SwagBraintreeApp_credit_card'),
);

criteria.setTotalCountMode(0);

void Repository.search(criteria).then(async (response: EntitySchema.EntityCollection<'payment_method'> | null) => {

    if (response === null || response.total !== 1)
        throw new Error('Payment method not found');


    const paymentMethod = response.first() as Entity<'payment_method'>;

    const params = new URLSearchParams(document.location.search);
    const apiContext = {
        ...await sw.context.getLanguage(),
    };

    Vue.use(VueI18n);
    Vue.use(DeviceHelperPlugin);
    Vue.directive('tooltip', TooltipDirective);

    store.commit('setPaymentMethod', paymentMethod);
    store.commit('setContext', params);
    store.commit('setApiContext', apiContext);

    const locale = await sw.context.getLocale();
    const i18n = new VueI18n({
        locale: locale.locale,
        fallbackLocale: locale.fallbackLocale,
        messages,
    });

    Vue.filter('toCurrency', function (value: number, currency: string = 'USD') {
        const formatter = new Intl.NumberFormat([locale.locale, locale.fallbackLocale], {
            style: 'currency',
            currency: currency,
        });

        return formatter.format(value);
    });

    Vue.filter('toDateTime', function (value: string, dateStyle: 'medium' | 'full' | 'long' | 'short' | undefined = undefined, timeStyle: 'medium' | 'full' | 'long' | 'short' | undefined = undefined) {
        const formatter = new Intl.DateTimeFormat([locale.locale, locale.fallbackLocale], {
            dateStyle: dateStyle,
            timeStyle: timeStyle,
        });

        return formatter.format(new Date(value));
    });

    await addLocations(paymentMethod, i18n);

    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
    Vue.prototype.$api = new Api();
    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
    Vue.prototype.$notify = new Notify(i18n);

    new Vue({
        el: '#app',
        components: {
            'sw-braintree-app-page-controller':
                () => import('./views/sw-braintree-app-page-controller.vue'),
        },
        store,
        i18n,
    });

    // Vue 3
    // const i18n = createI18n({
    //     locale: locale.locale,
    //     fallbackLocale: locale.fallbackLocale,
    //     messages: messages,
    //     legacy: false,
    // });
    //
    // const app = createApp({
    //     el: '#app',
    //     store,
    //     render: () => h(App),
    // });
    //
    // app
    //     .use(store, key)
    //     .use(i18n)
    //     .mount('#app')

    sw.location.startAutoResizer();
});
