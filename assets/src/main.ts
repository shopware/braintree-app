import * as sw from '@shopware-ag/meteor-admin-sdk';
import App from '@/App.vue';
import { createApp } from 'vue';
import { DeviceHelperPlugin, TooltipDirective } from '@shopware-ag/meteor-component-library';
import { createI18n } from '@/i18n';
import { createFilters } from '@/service/filters';
import { Api } from '@/service/api';
import { addLocations } from '@/location';
import { useStore } from '@/store';
import { Notify } from './service/notify';
import { createPinia } from 'pinia';

const Criteria = sw.data.Classes.Criteria;
const Repository = sw.data.repository<'payment_method'>('payment_method');
const criteria = new Criteria();

criteria
    .setTotalCountMode(0)
    .addFilter(
        Criteria.equals('handlerIdentifier', 'app\\SwagBraintreeApp_credit_card'),
    );

void Promise.all([
    sw.context.getLocale(),
    Repository.search(criteria),
]).then(async ([locale, response]) => {
    const paymentMethod = response?.first();
    if (response?.total !== 1 || !paymentMethod)
        throw new Error('Payment method not found');

    const i18n = createI18n(locale);
    const pinia = createPinia();

    const app = createApp(App)
        .use(pinia)
        .use(i18n)
        .use(DeviceHelperPlugin)
        .directive('tooltip', TooltipDirective);

    app.config.globalProperties.$api = new Api();
    app.config.globalProperties.$notify = new Notify(i18n);
    app.config.globalProperties.$filters = createFilters(locale);

    await addLocations(paymentMethod, i18n);

    useStore().setPaymentMethod(paymentMethod);

    app.mount('#app');

    sw.location.startAutoResizer();
});
