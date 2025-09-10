<?php declare(strict_types=1);

namespace Swag\Braintree\Framework;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Stopwatch\Stopwatch;

class Profiler
{
    private static ?Stopwatch $stopwatch = null;

    public function __construct(
        #[Autowire(service: 'debug.stopwatch')]
        ?Stopwatch $stopwatch = null,
    ) {
        self::$stopwatch = $stopwatch;
    }

    public static function trace(string $name, \Closure $closure): mixed
    {
        try {
            self::$stopwatch?->start($name, 'app');

            $result = $closure();
        } finally {
            self::$stopwatch?->stop($name, 'app');
        }

        return $result;
    }
}