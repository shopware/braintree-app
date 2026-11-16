import { test } from '@fixtures/AcceptanceTest';
import { expect } from '@shopware-ag/acceptance-test-suite';
import type { CheckoutConfirm } from '@page-objects/Storefront/Checkout/CheckoutConfirm';

/**
 * Submits the order form and expects the submission to be cancelled.
 *
 * The browser sends the order unless a submit listener cancels the event. The app's plugin cancels every submit
 * synchronously and only submits the form itself after the card was tokenized, which it never does with invalid fields.
 * A listener added now runs after the plugin's and records whether the event was cancelled on the form.
 */
async function submitOrderAndExpectItCancelled(checkoutConfirm: CheckoutConfirm) {
    const form = checkoutConfirm.page.locator('#confirmOrderForm');
    await form.evaluate((element) => {
        element.addEventListener('submit', (event) => {
            element.setAttribute('data-test-submit-cancelled', String(event.defaultPrevented));
        }, { once: true });
    });

    await checkoutConfirm.submitOrderButton.click();

    await expect(form).toHaveAttribute('data-test-submit-cancelled', 'true');
}

test('Customer cannot submit the order with empty card data.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
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
    await submitOrderAndExpectItCancelled(StorefrontCheckoutConfirm);

    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeCardNumberField).toHaveClass(/is-invalid/);
    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeExpirationDateField).toHaveClass(/is-invalid/);
    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeCvvField).toHaveClass(/is-invalid/);
    // prefilled from the customer
    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeCardholderNameField).not.toHaveClass(/is-invalid/);
    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreePostalCodeField).not.toHaveClass(/is-invalid/);

    await ShopCustomer.expects(StorefrontCheckoutConfirm.headline).toBeVisible();
    await ShopCustomer.expects(StorefrontCheckoutConfirm.submitOrderButton).toBeEnabled();
});

test('Customer cannot submit the order with an invalid card number and an expired card.', { tag: ['@Storefront'] }, async ({
    ShopCustomer,
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
    await submitOrderAndExpectItCancelled(StorefrontCheckoutConfirm);

    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeCardNumberField).toHaveClass(/is-invalid/);
    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeExpirationDateField).toHaveClass(/is-invalid/);
    await ShopCustomer.expects(StorefrontCheckoutConfirm.braintreeCvvField).not.toHaveClass(/is-invalid/);

    await ShopCustomer.expects(StorefrontCheckoutConfirm.headline).toBeVisible();
    await ShopCustomer.expects(StorefrontCheckoutConfirm.submitOrderButton).toBeEnabled();
});
