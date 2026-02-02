<?php declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

// https://github.com/symfony/symfony/issues/53812#issuecomment-1962311843
ErrorHandler::register(null, false);

require \dirname(__DIR__) . '/vendor/autoload.php';

/** @phpstan-ignore function.alreadyNarrowedType */
if (\method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(\dirname(__DIR__) . '/.env');
}

VarDumper::setHandler(static function (mixed $var): ?string {
    $cloner = new VarCloner();
    $dumper = 'cli' === \PHP_SAPI ? new CliDumper() : new HtmlDumper();

    return $dumper->dump($cloner->cloneVar($var));
});
