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

test('Customer fails an enforced 3D Secure challenge, the payment fails and the customer pays the order on the edit order page.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
    TestDataService,
    StorefrontCheckoutConfirm,
    StorefrontCheckoutOrderEdit,
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
    await ShopCustomer.attemptsTo(FillBraintreeCard(TestCards.failedChallenge));
    await ShopCustomer.presses(StorefrontCheckoutConfirm.submitOrderButton);
    await ShopCustomer.attemptsTo(CompleteThreeDSecureChallenge());

    // Braintree hands out a nonce with status authenticate_failed, which the app rejects because 3D Secure is enforced
    await ShopCustomer.expects(StorefrontCheckoutOrderEdit.paymentFailedAlert).toBeVisible();

    const orderId = StorefrontCheckoutOrderEdit.getOrderId();
    expect(orderId).not.toBe('');
    expect(await ReadOrderTransactionState(orderId)()).toBe('failed');

    // The edit order page keeps the card payment selected and loads the card form again
    await ShopCustomer.expects(StorefrontCheckoutOrderEdit.paymentMethodRadioGroup.getByRole('radio', { name: StorefrontCheckoutConfirm.paymentBraintreeName })).toBeChecked();
    await ShopCustomer.attemptsTo(FillBraintreeCard(TestCards.successfulNoChallenge));
    await ShopCustomer.presses(StorefrontCheckoutOrderEdit.completePaymentButton);
    // 3D Secure runs before the order is sent and can outlast the expect timeout when Cardinal's sandbox device
    // fingerprinting fails and it waits for its own timeouts, so wait for the navigation like ATS's SubmitOrder does
    await StorefrontCheckoutFinish.page.waitForURL('**/checkout/finish**');

    // the finish page of a changed payment greets differently than the one of a new order
    await ShopCustomer.expects(StorefrontCheckoutFinish.page.getByRole('heading', { name: 'Thank you for updating your order!' })).toBeVisible();
    expect(StorefrontCheckoutFinish.getOrderId()).toBe(orderId);
    expect(await ReadOrderTransactionState(orderId)()).toBe('paid');
});
