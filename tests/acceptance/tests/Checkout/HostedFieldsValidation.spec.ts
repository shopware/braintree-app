import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';

test('Customer cannot submit the order with empty card data.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
    AdminApiContext,
    TestDataService,
    StorefrontCheckoutConfirm,
    Login,
    ProceedToBraintreeCheckout,
    ConfirmTermsAndConditions,
}) => {
    const product = await TestDataService.createBasicProduct();
    const customer = await TestDataService.createCustomer();

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));
    await ShopCustomer.attemptsTo(ConfirmTermsAndConditions());
    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeCardNumberInput).toBeVisible();
    await ShopCustomer.presses(StorefrontCheckoutConfirm.submitOrderButton);

    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeCardNumberField).toHaveClass(/is-invalid/);
    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeExpirationDateField).toHaveClass(/is-invalid/);
    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeCvvField).toHaveClass(/is-invalid/);
    // prefilled from the customer
    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeCardholderNameField).not.toHaveClass(/is-invalid/);
    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreePostalCodeField).not.toHaveClass(/is-invalid/);

    await ShopCustomer.expects(StorefrontCheckoutConfirm.headline).toBeVisible();
    await ShopCustomer.expects(StorefrontCheckoutConfirm.submitOrderButton).toBeEnabled();

    const orderResponse = await AdminApiContext.post('search-ids/order', {
        data: { filter: [{ type: 'equals', field: 'orderCustomer.customerId', value: customer.id }] },
    });
    expect(orderResponse.ok()).toBeTruthy();
    expect((await orderResponse.json() as { total: number }).total).toBe(0);
});

test('Customer cannot submit the order with an invalid card number and an expired card.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
    AdminApiContext,
    TestDataService,
    StorefrontCheckoutConfirm,
    Login,
    ProceedToBraintreeCheckout,
    ConfirmTermsAndConditions,
    FillBraintreeCard,
}) => {
    const product = await TestDataService.createBasicProduct();
    const customer = await TestDataService.createCustomer();

    await ShopCustomer.attemptsTo(Login(customer));
    await ShopCustomer.attemptsTo(ProceedToBraintreeCheckout(product));
    await ShopCustomer.attemptsTo(ConfirmTermsAndConditions());
    // the Visa test number 4111111111111111 with a broken Luhn check digit
    await ShopCustomer.attemptsTo(FillBraintreeCard({ number: '4111111111111112', expirationDate: '01/2001', cvv: '123' }));
    await ShopCustomer.presses(StorefrontCheckoutConfirm.submitOrderButton);

    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeCardNumberField).toHaveClass(/is-invalid/);
    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeExpirationDateField).toHaveClass(/is-invalid/);
    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeCvvField).not.toHaveClass(/is-invalid/);

    await ShopCustomer.expects(StorefrontCheckoutConfirm.headline).toBeVisible();
    await ShopCustomer.expects(StorefrontCheckoutConfirm.submitOrderButton).toBeEnabled();

    const orderResponse = await AdminApiContext.post('search-ids/order', {
        data: { filter: [{ type: 'equals', field: 'orderCustomer.customerId', value: customer.id }] },
    });
    expect(orderResponse.ok()).toBeTruthy();
    expect((await orderResponse.json() as { total: number }).total).toBe(0);
});
