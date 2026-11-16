import { mergeTests } from '@shopware-ag/acceptance-test-suite';
import { FillBraintreeCard, type FillBraintreeCardTask } from './ShopCustomer/Checkout/FillBraintreeCard';
import { ProceedToBraintreeCheckout, type ProceedToBraintreeCheckoutTask } from './ShopCustomer/Checkout/ProceedToBraintreeCheckout';

export interface ShopCustomerTasks {
    FillBraintreeCard: FillBraintreeCardTask
    ProceedToBraintreeCheckout: ProceedToBraintreeCheckoutTask
}

export const test = mergeTests(
    FillBraintreeCard,
    ProceedToBraintreeCheckout,
);
