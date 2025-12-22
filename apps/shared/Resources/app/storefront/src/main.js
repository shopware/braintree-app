import SwagBraintreeHostedFields from './checkout/swag-braintree.hosted-fields';

window.PluginManager.register(
    'SwagBraintreeHostedFields',
    SwagBraintreeHostedFields,
    '[data-swag-braintree-hosted-fields]'
);
