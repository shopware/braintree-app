import { test as ShopwareTestSuite, mergeTests } from '@shopware-ag/acceptance-test-suite';
import { test as braintreeShopCustomerTasks } from '@tasks/ShopCustomerTasks';
import { test as braintreeShopAdminTasks } from '@tasks/ShopAdminTasks';
import { test as braintreeAdminPages } from '@page-objects/AdministrationPages';
import { test as braintreeStorefrontPages } from '@page-objects/StorefrontPages';
import { test as services } from '@services/Services';
import { test as testShippingMethod } from './TestShippingMethod';

export * from '@shopware-ag/acceptance-test-suite';

export const test = mergeTests(
    ShopwareTestSuite,
    braintreeShopCustomerTasks,
    braintreeShopAdminTasks,
    braintreeAdminPages,
    braintreeStorefrontPages,
    services,
    testShippingMethod,
);
