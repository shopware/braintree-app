<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Monolog\Formatter\LineFormatter;
use Swag\Braintree\Braintree\Gateway\BraintreeGatewayFactory;
use Swag\Braintree\Braintree\Util\ReportClientFactory;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services
        ->load('Swag\\Braintree\\', '../src/*')
        ->exclude('../src/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}');

    $container->services()
        ->set('monolog.formatter.app_request', LineFormatter::class)
        ->args(["[%%datetime%%] [%%context.debugId%%] [%%context.shopId%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n"]);

    $container->services()
        ->set('Braintree\Gateway')
        ->factory([BraintreeGatewayFactory::class, 'createBraintreeGateway']);

    $container->services()
        ->set('guzzle.client.report')
        ->factory([ReportClientFactory::class, 'createClient'])
        ->arg('$config', ['base_uri' => 'https://api.shopware.com']);
};
