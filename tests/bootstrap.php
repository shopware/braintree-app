<?php declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

require \dirname(__DIR__) . '/vendor/autoload.php';

if (\file_exists(\dirname(__DIR__) . '/config/bootstrap.php')) {
    require \dirname(__DIR__) . '/config/bootstrap.php';
} elseif (\method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(\dirname(__DIR__) . '/.env');
}

VarDumper::setHandler(function (mixed $var): ?string {
    $cloner = new VarCloner();
    $dumper = 'cli' === \PHP_SAPI ? new CliDumper() : new HtmlDumper();

    return $dumper->dump($cloner->cloneVar($var));
});
