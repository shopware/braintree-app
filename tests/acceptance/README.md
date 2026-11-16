## Setup

The suite needs a running Shopware 6.7 shop with the SwagBraintreeApp installed, active and registered with a reachable app server.

Navigate to this directory and install the dependencies and the browser.

```
cd tests/acceptance
npm ci
npx playwright install chromium
```

Copy `.env.dist` to `.env` and fill it in. Every value can also be passed as an environment variable.

```
SHOPWARE_URL="<shop base url, default http://localhost:8000>"
BRAINTREE_MERCHANT_ID="<...>"
BRAINTREE_PUBLIC_KEY="<...>"
BRAINTREE_PRIVATE_KEY="<...>"

# optional, defaults to the admin user admin/shopware
SHOPWARE_ACCESS_KEY_ID="<your-api-client-id>"
SHOPWARE_SECRET_ACCESS_KEY="<your-api-secret>"
```

If the shop runs in `dev`, run `bin/console cache:clear` in the shop after installing the app: Shopware loads app storefront templates in `dev` from the compiled container, so the Hosted Fields are missing until it is rebuilt.

To generate the access key you can use `bin/console integration:create AcceptanceTest --admin` in the shop.

### `SHOPWARE_URL`, not `APP_URL`

The acceptance test suite reads the shop URL from `APP_URL`, but in this repository (and its devenv shell) `APP_URL` is the URL of the Braintree app server.
`playwright.config.ts` therefore always overwrites `APP_URL` with `SHOPWARE_URL`. It also pins `LANG` to `en-US`, which the acceptance test suite maps to the language en-GB, the country US and the currency USD for its test sales channel, regardless of your locale.

### Braintree sandbox

- Sandbox API keys (Braintree Control Panel > Settings > API).
- A merchant account in USD.
- The setup connects the keys, maps USD to that merchant account and turns off enforced 3D Secure on the default level of the shop. This overwrites whatever Braintree settings the shop had.

## Running Tests

```
npx playwright test
```

The `Braintree Setup` project runs first and prepares the Braintree connection and the payment method, the `Braintree` project contains the specs.

## Writing Tests

Import `test` from `@fixtures/AcceptanceTest`. Page objects and tasks are registered in `page-objects/AdministrationPages.ts`, `page-objects/StorefrontPages.ts`, `tasks/ShopAdminTasks.ts` or `tasks/ShopCustomerTasks.ts`.
Use the `BraintreeAppClient` fixture to arrange the app's configuration instead of clicking through its admin module, and restore it in `test.afterEach` (a `finally` block does not run when a test times out). `BraintreeBaseline.establish()` restores the state the setup project established.

Every spec file that sends the API keys (`BraintreeAppClient.connect()` and `BraintreeBaseline.establish()`, also in hooks) or opens the payment overview card or the order's Braintree tab must start with `test.use({ trace: 'off', screenshot: 'off' })`: traces and screenshots end up in `test-results/`, which CI uploads.
