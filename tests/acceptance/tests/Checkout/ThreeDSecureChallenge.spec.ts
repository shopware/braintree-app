import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';
import { createPricedProduct } from '@services/PricedProduct';
import { TestCards } from '@services/TestCards';

test.beforeEach(async ({ BraintreeAppClient, DefaultSalesChannel }) => {
    await BraintreeAppClient.setThreeDSecureEnforced(DefaultSalesChannel.salesChannel.id, true);
});

// a hook instead of finally: it also runs when the test times out, before the app client is disposed
test.afterEach(async ({ BraintreeAppClient, DefaultSalesChannel }) => {
    await BraintreeAppClient.setThreeDSecureEnforced(DefaultSalesChannel.salesChannel.id, null);
});

test('Customer passes an enforced 3D Secure challenge and the order is paid.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
    TestDataService,
    StorefrontCheckoutConfirm,
    StorefrontCheckoutFinish,
    Login,
    ProceedToBraintreeCheckout,
    ConfirmTermsAndConditions,
    FillBraintreeCard,
    CompleteThreeDSecureChallenge,
    ReadOrderTransactionState,
}) => {
    const product = await createPricedProduct(TestDataService, 10, 1010);
    const customer = await TestDataService.createCustomer();

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));
    await ShopCustomer.attemptsTo(ConfirmTermsAndConditions());
    await ShopCustomer.attemptsTo(FillBraintreeCard(TestCards.successfulChallenge));
    await ShopCustomer.presses(StorefrontCheckoutConfirm.submitOrderButton);
    await ShopCustomer.attemptsTo(CompleteThreeDSecureChallenge());
    await ShopCustomer.expects(StorefrontCheckoutFinish.headline).toBeVisible();

    const orderId = StorefrontCheckoutFinish.getOrderId();
    expect(orderId).not.toBe('');
    expect(await ReadOrderTransactionState(orderId)()).toBe('paid');
});

test('Customer fails an enforced 3D Secure challenge and the payment of the order fails.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
    TestDataService,
    StorefrontCheckoutConfirm,
    StorefrontCheckoutOrderEdit,
    Login,
    ProceedToBraintreeCheckout,
    ConfirmTermsAndConditions,
    FillBraintreeCard,
    CompleteThreeDSecureChallenge,
    ReadOrderTransactionState,
}) => {
    const product = await createPricedProduct(TestDataService, 10, 1010);
    const customer = await TestDataService.createCustomer();

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));
    await ShopCustomer.attemptsTo(ConfirmTermsAndConditions());
    await ShopCustomer.attemptsTo(FillBraintreeCard(TestCards.failedChallenge));
    await ShopCustomer.presses(StorefrontCheckoutConfirm.submitOrderButton);
    await ShopCustomer.attemptsTo(CompleteThreeDSecureChallenge());

    // Braintree hands out a nonce with status authenticate_failed, which the app rejects because 3D Secure is enforced
    await ShopCustomer.expects(StorefrontCheckoutOrderEdit.paymentFailedAlert).toBeVisible();

    const orderId = StorefrontCheckoutOrderEdit.getOrderId();
    expect(orderId).not.toBe('');
    expect(await ReadOrderTransactionState(orderId)()).toBe('failed');
});
