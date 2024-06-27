<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Integration;

use Swag\Braintree\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\ErrorHandler\ErrorHandler;

class BootstrapTest extends KernelTestCase
{
    public function test(): void
    {
        static::bootKernel();
        static::assertTrue(static::getContainer()->has(Kernel::class));

        $handler = new ErrorHandler();

        // https://github.com/symfony/symfony/issues/53812
        /** @phpstan-ignore-next-line */
        set_exception_handler([new ErrorHandler(), 'handleException']);
    }
}
