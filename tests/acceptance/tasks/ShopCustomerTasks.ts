import { mergeTests } from '@shopware-ag/acceptance-test-suite';
import { CompleteThreeDSecureChallenge, type CompleteThreeDSecureChallengeTask } from './ShopCustomer/Checkout/CompleteThreeDSecureChallenge';
import { FillBraintreeCard, type FillBraintreeCardTask } from './ShopCustomer/Checkout/FillBraintreeCard';
import { ProceedToBraintreeCheckout, type ProceedToBraintreeCheckoutTask } from './ShopCustomer/Checkout/ProceedToBraintreeCheckout';

export interface ShopCustomerTasks {
    CompleteThreeDSecureChallenge: CompleteThreeDSecureChallengeTask
    FillBraintreeCard: FillBraintreeCardTask
    ProceedToBraintreeCheckout: ProceedToBraintreeCheckoutTask
}

export const test = mergeTests(
    CompleteThreeDSecureChallenge,
    FillBraintreeCard,
    ProceedToBraintreeCheckout,
);
