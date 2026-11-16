import type { Page, Locator } from '@shopware-ag/acceptance-test-suite';
import { StorefrontPageObjects } from '@shopware-ag/acceptance-test-suite';

/** Shopware redirects here when the payment of a just placed order fails */
export class CheckoutOrderEdit extends StorefrontPageObjects.CheckoutOrderEdit {
    public readonly paymentFailedAlert: Locator;

    constructor(page: Page) {
        super(page);

        this.paymentFailedAlert = page.getByRole('alert').filter({ hasText: 'we were not able to process your payment' });
    }

    getOrderId(): string {
        return new URL(this.page.url()).pathname.match(/\/account\/order\/edit\/([0-9a-f]{32})$/)?.[1] ?? '';
    }
}
