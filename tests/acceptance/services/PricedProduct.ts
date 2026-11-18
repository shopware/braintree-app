import type { FixtureTypes, Product } from '@shopware-ag/acceptance-test-suite';

/**
 * The sandbox decides the outcome of a transaction by its amount and may reject a repeated amount with the same card
 * as a duplicate, so the price is random within the range: https://developer.paypal.com/braintree/docs/reference/general/testing/
 * The test shipping method is free, so the order total equals the price.
 */
export async function createPricedProduct(TestDataService: FixtureTypes['TestDataService'], min: number, max: number): Promise<Product> {
    const gross = Math.round((min + Math.random() * (max - min)) * 100) / 100;
    const net = Math.round(gross / 1.19 * 100) / 100;

    return TestDataService.createBasicProduct({
        price: [TestDataService.defaultCurrencyId, TestDataService.defaultSalesChannel.currencyId]
            .map(currencyId => ({ currencyId, gross, net, linked: false })),
    });
}
