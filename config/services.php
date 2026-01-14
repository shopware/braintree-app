<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Monolog\Formatter\LineFormatter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->load('Swag\\Braintree\\', '../src/*')
        ->exclude('../src/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}');

    $services
        ->set('monolog.formatter.app_request', LineFormatter::class)
        ->args(["[%%datetime%%] [%%context.debugId%%] [%%context.shopId%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n"]);

    $services->set(\Braintree\Gateway::class)
        ->factory([service(\Swag\Braintree\Braintree\Gateway\BraintreeGatewayFactory::class), 'createBraintreeGateway']);

    $services->set('guzzle.client.report', \GuzzleHttp\Client::class)
        ->args([['base_uri' => 'https://api.shopware.com']])
        ->factory([\Swag\Braintree\Braintree\Util\ReportClientFactory::class, 'createClient']);
};
