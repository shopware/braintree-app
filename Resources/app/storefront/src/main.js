import SwagBraintreeHostedFields from './checkout/swag-braintree.hosted-fields';
import SwagBraintreeExpress from "./checkout/swag-braintree.express";

window.PluginManager.register(
    'SwagBraintreeHostedFields',
    SwagBraintreeHostedFields,
    '[data-swag-braintree-hosted-fields]'
);

window.PluginManager.register(
    'SwagBraintreeExpress',
    SwagBraintreeExpress,
    '[data-swag-braintree-express]'
);
