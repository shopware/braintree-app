# SwagBraintreeApp

[![codecov](https://codecov.io/gh/shopware/braintree-app/graph/badge.svg?token=4J4BIGPUJH)](https://codecov.io/gh/shopware/braintree-app)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fshopware%2Fbraintree-app%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/shopware/braintree-app/main)

## Local Setup
- First setup [devenv](https://developer.shopware.com/docs/guides/installation/devenv.html)
- [Replace urls](###-Replace-hardcoded-urls)
- Run `devenv up`
- Run `composer setup`
- Install and activate the app
- Rebuild shopware's storefront js

### Replace hardcoded urls
There are several hardcoded urls that need to be replaced when developing locally:
- `manifest.xml`
- `assets/src/service/api.ts`
- `Resources/app/storefront/src/checkout/swag-braintree.hosted-fields.js`

Replace `https://braintree.shopware.com` with your `APP_URL`, e.g. `http://localhost:8080`

## How to develop
### Administation
- Build dev: `npm run dev`
- Build prod: `npm run build`
- Watch dev: `npm run watch`
- ESLint: `composer eslint` or `composer eslint-fix`

### App Server
- ECS: `composer ecs-fix`
- PHPStan: `composer phpstan`
- PHPUnit: `composer phpunit`
- Infection: `composer infection`
