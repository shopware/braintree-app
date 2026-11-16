import type { Page, Locator } from '@shopware-ag/acceptance-test-suite';

/**
 * The Braintree tab of the order detail, an admin-sdk iframe of the app server.
 */
export class BraintreeOrderTransactionDetail {
    public readonly tabLink: Locator;

    public readonly status: Locator;
    public readonly transactionId: Locator;
    public readonly liabilityShiftPossible: Locator;
    public readonly liabilityShifted: Locator;
    public readonly controlPanelLink: Locator;

    constructor(page: Page) {
        this.tabLink = page.getByRole('tab', { name: 'Braintree' });

        const frame = page.frameLocator('iframe[src*="location-id=swag-braintree-app-order-transaction-detail-position-before"]');
        const paymentDetails = frame.locator('.transaction__body__payment-detail__payment__detail');
        this.status = paymentDetails.locator('.sw-status__text');
        this.transactionId = paymentDetails.locator('.transaction__body__payment-detail__payment__detail__transaction-id');
        const threeDSecure = frame.locator('.transaction__body__transaction-detail__three-d-s__detail');
        this.liabilityShiftPossible = threeDSecure.locator('.transaction__body__transaction-detail__three-d-s__detail__liability-possible');
        this.liabilityShifted = threeDSecure.locator('.transaction__body__transaction-detail__three-d-s__detail__liability-shifted');
        this.controlPanelLink = frame.getByRole('link', { name: 'View and edit transaction in Braintree' });
    }
}
