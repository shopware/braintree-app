<p align="center">
  <img src="https://raw.githubusercontent.com/shopware/braintree-app/trunk/assets/img/braintree-logo.webp" width="100" />
</p>
<p align="center">
    <h1 align="center">BRAINTREE APP</h1>
</p>
<p align="center">
    <em>Cloud ready Braintree payment provider for Shopware</em>
</p>
<p align="center">
        <img src="https://img.shields.io/github/license/shopware/braintree-app?style=default&color=0080ff" alt="license">
<p>
<p align="center">
        <a href="https://codecov.io/gh/shopware/braintree-app"><img src="https://codecov.io/gh/shopware/braintree-app/graph/badge.svg?token=4J4BIGPUJH"/></a>
        <a href="https://dashboard.stryker-mutator.io/reports/github.com/shopware/braintree-app/main"><img src="https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fshopware%2Fbraintree-app%2Fmain"></a>
</p>
<hr>

##  Quick Links

> - [ Overview](#overview)
> - [ Features](#features)
> - [ Getting Started](#getting-started)
>   - [ Installation](#local-installation)
>   - [ Replace hardcoded urls](#replace-hardcoded-urls)
>   - [ Develop locally](#develop-locally)
>   - [ Tests](#tests)
> - [ Contributing](#contributing)
> - [ License](#license)

---

##  Overview

With the “PayPal Braintree” app, you can now easily integrate one of the most popular solutions for credit card payments into your store. With “PayPal Braintree”, you also benefit from the acceptance, security and scalability of one of the world’s largest payment providers and reach more than 200 million customers.

The app adds the "Braintree" payment method to Shopware shops, allowing merchants to integrate credit card payments for their customers. It is intended to be used by US merchants.

This repository encompasses both the robust backend infrastructure and the corresponding app. The backend forms the core of the application, while the app seamlessly integrates into Shopware shops.

---

##  Features

:white_check_mark: Credit card payments via Braintree available as app for the first time

:white_check_mark: Easy integration & maintenance

:white_check_mark: Maximum security thanks to automatic 3-D Secure

---

##  Getting Started

If you are a merchant and want to install the app to your shop, you can find it [here](https://store.shopware.com/en/swag930601467972f/braintree-by-paypal.html)

###  Local Installation

1. First setup [devenv](https://developer.shopware.com/docs/guides/installation/devenv.html)
2. Clone the repository
   ```sh
   git clone https://github.com/shopware/braintree-app
   ```
3. Install dependencies
   ```sh
   composer install
   npm install
   ```
4. Setup database, manifest and hardcoded urls
   ```sh
   composer setup
   ```
5. Run
   ```sh
   devenv up
   ```
6. Install and activate the app
7. Rebuild shopware's storefront js

### Replace hardcoded urls

#### Via command

Run `composer setup:manifest` to generate a manifest setup with the current `APP_URL`
Run `composer setup:url` to replace hardcoded urls with your `APP_URL`, e.g. `http://localhost:8080`.

#### Manually

There are several hardcoded urls that need to be replaced when developing locally:
- ~~`manifest.xml`~~ (Replaced by manifest templating with `composer setup:manifest`)
- ~~`assets/src/service/api.ts`~~ (Replaced by origin url)
- `Resources/app/storefront/src/checkout/swag-braintree.hosted-fields.js`
  
Replace `https://braintree.shopware.com` with your `APP_URL`, e.g. `http://localhost:8080`

### Develop locally

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

###  Tests

To execute tests, run:

```sh
composer phpunit
```

This repository uses [mutation testing](https://infection.github.io/guide/) to determine effectiveness of unit tests.

To test for mutations, run:

```sh
composer infection
```

---

##  Contributing

Contributions are welcome! Here are several ways you can contribute:

- **Submit Pull Requests**: Review open PRs, and submit your own PRs.
- **[Report Issues](https://github/shopware/braintree-app/issues)**: Submit bugs found or log feature requests for Braintree-app.

<details closed>
    <summary>Pull request guideline</summary>

1. **Fork the Repository**: Start by forking the project repository to your GitHub account.
2. **Clone Locally**: Clone the forked repository to your local machine using a Git client.
   ```sh
   git clone https://github.com/shopware/braintree-app
   ```
3. **Create a New Branch**: Always work on a new branch, giving it a descriptive name.
   ```sh
   git checkout -b new-feature-x
   ```
4. **Make Your Changes**: Develop and test your changes locally.
5. **Commit Your Changes**: Commit with a clear message describing your updates.
   ```sh
   git commit -m 'Implemented new feature x.'
   ```
6. **Push to GitHub**: Push the changes to your forked repository.
   ```sh
   git push origin new-feature-x
   ```
7. **Submit a Pull Request**: Create a PR against the original project repository. Clearly describe the changes and their motivations.

Once your PR is reviewed and approved, it will be merged into the trunk branch.

</details>

---

##  License

The braintree app is completely free and released under the [MIT](https://github.com/shopware/braintree-app/blob/trunk/LICENSE) License.
