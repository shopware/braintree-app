<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Command;

use Swag\Braintree\Command\ManifestGenerateCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * Test Helper for the manifest generation command
 * Changes the manifest file path to a cache path to not cluster the project directory
 *
 * @internal
 *
 * @infection-ignore-all
 */
#[AsCommand(name: 'manifest:generate')]
#[When(env: 'test')]
class TestManifestGenerateCommand extends ManifestGenerateCommand
{
    protected function manifestExists(): bool
    {
        return \file_exists($this->projectDir . '/var/cache/test/test_manifest.xml');
    }

    protected function writeManifest(string $manifest): bool
    {
        return \file_put_contents($this->projectDir . '/var/cache/test/test_manifest.xml', $manifest) !== false;
    }
}
