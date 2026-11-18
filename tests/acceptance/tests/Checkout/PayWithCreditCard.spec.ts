import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';
import { createPricedProduct } from '@services/PricedProduct';
import { TestCards } from '@services/TestCards';

test('Customer buys a product and pays with a credit card via Braintree.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
    TestDataService,
    StorefrontCheckoutFinish,
    Login,
    ProceedToBraintreeCheckout,
    ConfirmTermsAndConditions,
    FillBraintreeCard,
    SubmitOrder,
    ReadOrderTransactionState,
}) => {
    const product = await createPricedProduct(TestDataService, 10, 1010);
    const customer = await TestDataService.createCustomer();

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));
    await ShopCustomer.attemptsTo(ConfirmTermsAndConditions());
    await ShopCustomer.attemptsTo(FillBraintreeCard(TestCards.successfulNoChallenge));
    await ShopCustomer.attemptsTo(SubmitOrder());

    const orderId = StorefrontCheckoutFinish.getOrderId();
    expect(orderId).not.toBe('');
    expect(await ReadOrderTransactionState(orderId)()).toBe('paid');
});
