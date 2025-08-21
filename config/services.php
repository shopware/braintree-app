<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Monolog\Formatter\LineFormatter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services
        ->load('Swag\\Braintree\\', '../src/*')
        ->exclude('../src/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}');

    $container->import(__DIR__ . '/services/braintree.xml', 'xml');

    $container->services()
        ->set('monolog.formatter.app_request', LineFormatter::class)
        ->args(["[%%datetime%%] [%%context.debugId%%] [%%context.shopId%%] %%channel%%.%%level_name%%: %%message%% %%context%% %%extra%%\n"]);
};
