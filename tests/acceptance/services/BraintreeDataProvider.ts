export type BraintreeDataKey = 'MERCHANT_ID' | 'PUBLIC_KEY' | 'PRIVATE_KEY';

/**
 * Reads the Braintree sandbox credentials from the BRAINTREE_* environment variables.
 */
export class BraintreeDataProvider {
    public get(key: BraintreeDataKey): string {
        const value = process.env[`BRAINTREE_${key}`];

        if (!value) {
            throw new Error(`Env BRAINTREE_${key} is not set. Add it to tests/acceptance/.env or export it.`);
        }

        return value;
    }
}
