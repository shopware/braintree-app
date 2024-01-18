# SwagBraintreeApp

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
