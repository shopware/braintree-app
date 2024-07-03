<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Integration;

use Swag\Braintree\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BootstrapTest extends KernelTestCase
{
    public function test(): void
    {
        static::bootKernel();
        static::assertTrue(static::getContainer()->has(Kernel::class));
    }
}
