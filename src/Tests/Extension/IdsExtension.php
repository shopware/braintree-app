<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Extension;

use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\SkippedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Swag\Braintree\Tests\Ids;

class IdsExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new class implements PreparationStartedSubscriber {
            public function notify(PreparationStarted $event): void
            {
                Ids::reset();
            }
        });

        $facade->registerSubscriber(new class implements SkippedSubscriber {
            public function notify(Skipped $event): void
            {
                Ids::reset();
            }
        });

        $facade->registerSubscriber(new class implements FinishedSubscriber {
            public function notify(Finished $event): void
            {
                Ids::reset();
            }
        });

        $facade->registerSubscriber(new class implements ErroredSubscriber {
            public function notify(Errored $event): void
            {
                Ids::reset();
            }
        });
    }
}
