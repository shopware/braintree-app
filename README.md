# SwagBraintreeApp

**A payment app for [Shopware 6](https://github.com/shopware/shopware) build upon the [App Bundle](https://github.com/shopware/app-bundle-symfony) with [Symfony](https://symfony.com/), [Doctrine](https://www.doctrine-project.org/) and [Vue.js](https://vuejs.org/).**  
It serves as an example for how to implement a payment provider with the app system.

## Setup locally
1. Setup Shopware ~6.5
2. Setup [devenv](https://developer.shopware.com/docs/guides/installation/devenv.html)
3. Run `devenv up`
4. Run `composer setup`
5. Install and activate the app
6. Rebuild shopware's storefront js

### How to develop
#### Administation
- Build dev: `npm run dev`
- Build prod: `npm run build`
- Watch dev: `npm run watch`
- ESLint: `composer eslint` or `composer eslint-fix`

#### App Server
- ECS: `composer ecs-fix`
- PHPStan: `composer phpstan`
- PHPUnit: `composer phpunit`
- Infection: `composer infection`
