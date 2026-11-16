import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';
import { createPricedProduct } from '@services/PricedProduct';
import { TestCards } from '@services/TestCards';

test('Customer pays with a credit card the processor declines and the payment of the order fails.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
    TestDataService,
    StorefrontCheckoutConfirm,
    StorefrontCheckoutOrderEdit,
    Login,
    ProceedToBraintreeCheckout,
    ConfirmTermsAndConditions,
    FillBraintreeCard,
    ReadOrderTransactionState,
}) => {
    // The sandbox declines 2000.00-2999.99 with the processor response of the amount, 2000 is "Do Not Honor"
    const product = await createPricedProduct(TestDataService, 2000.01, 2000.99);
    const customer = await TestDataService.createCustomer();

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));
    await ShopCustomer.attemptsTo(ConfirmTermsAndConditions());
    await ShopCustomer.attemptsTo(FillBraintreeCard(TestCards.successfulNoChallenge));
    await ShopCustomer.presses(StorefrontCheckoutConfirm.submitOrderButton);

    await ShopCustomer.expects(StorefrontCheckoutOrderEdit.paymentFailedAlert).toBeVisible();

    const orderId = StorefrontCheckoutOrderEdit.getOrderId();
    expect(orderId).not.toBe('');
    expect(await ReadOrderTransactionState(orderId)()).toBe('failed');
});
