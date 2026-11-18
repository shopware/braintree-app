import type { FixtureTypes as BaseTypes } from '@shopware-ag/acceptance-test-suite';
import type { AdminPageTypes } from '@page-objects/AdministrationPages';
import type { StorefrontPageTypes } from '@page-objects/StorefrontPages';
import type { ShopAdminTasks } from '@tasks/ShopAdminTasks';
import type { ShopCustomerTasks } from '@tasks/ShopCustomerTasks';
import type { ServicesTypes } from '@services/Services';
import type { TestShippingMethodTypes } from '@fixtures/TestShippingMethod';

interface TestFixtureTypes extends AdminPageTypes, StorefrontPageTypes, ShopAdminTasks, ShopCustomerTasks, ServicesTypes, TestShippingMethodTypes {
}

declare global {
    type Task<Args extends Array<unknown>> = (...args: Args) => () => Promise<void>;

    interface FixtureTypes extends Omit<BaseTypes, keyof TestFixtureTypes>, TestFixtureTypes {
    }
}
