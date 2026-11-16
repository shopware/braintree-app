import { defineConfig, devices } from '@playwright/test';
import dotenv from 'dotenv';
import * as process from 'node:process';

dotenv.config({ quiet: true });

process.env['SHOPWARE_ADMIN_USERNAME'] = process.env['SHOPWARE_ADMIN_USERNAME'] || 'admin';
process.env['SHOPWARE_ADMIN_PASSWORD'] = process.env['SHOPWARE_ADMIN_PASSWORD'] || 'shopware';

// The acceptance test suite reads APP_URL as the Shopware URL, but the devenv shell sets it to the Braintree app server
process.env['APP_URL'] = (process.env['SHOPWARE_URL'] || 'http://localhost:8000').replace(/\/+$/, '') + '/';
process.env['ADMIN_URL'] = process.env['APP_URL'] + 'admin/';

// The acceptance test suite derives language, country and currency of its sales channel from LANG
process.env['LANG'] = 'en-US';

export default defineConfig({
    testDir: './tests',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    // The Braintree connection is shop-level state shared by all tests
    workers: 1,
    reporter: 'html',
    timeout: 60_000,
    expect: {
        timeout: 15_000,
    },

    use: {
        baseURL: process.env['APP_URL'],
        trace: 'retain-on-failure',
        video: 'off',
        locale: 'en-US',
    },

    // We abuse this to wait for the external webserver
    webServer: {
        command: 'sleep 1d',
        url: process.env['APP_URL'],
        reuseExistingServer: true,
    },

    projects: [
        {
            name: 'Braintree Setup',
            testMatch: /.*\.setup\.ts/,
            use: { trace: 'off' },
        },
        {
            name: 'Braintree',
            testMatch: /.*\.spec\.ts/,
            use: {
                ...devices['Desktop Chrome'],
                channel: 'chromium',
            },
            dependencies: ['Braintree Setup'],
        },
    ],
});
