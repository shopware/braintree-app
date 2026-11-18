import type { BraintreeCredentials } from './BraintreeAppClient';

export class BraintreeDataProvider {
    public getCredentials(): BraintreeCredentials {
        return {
            merchantId: this.get('BRAINTREE_MERCHANT_ID'),
            publicKey: this.get('BRAINTREE_PUBLIC_KEY'),
            privateKey: this.get('BRAINTREE_PRIVATE_KEY'),
        };
    }

    private get(name: string): string {
        const value = process.env[name];

        if (!value) {
            throw new Error(`Env ${name} is not set. Add it to tests/acceptance/.env or export it.`);
        }

        return value;
    }
}
