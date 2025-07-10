<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Command\ManifestGenerateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

#[CoversClass(ManifestGenerateCommand::class)]
class ManifestGenerateCommandTest extends TestCase
{
    private Environment&MockObject $twig;

    private InputInterface&MockObject $input;

    private OutputInterface&MockObject $output;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    #[DataProvider('provideFailOnAnyParameterMissing')]
    public function testFailOnAnyParameterMissing(
        ?string $appUrl,
        ?string $appSecret,
        ?string $environment,
        ?string $projectDir,
    ): void {
        $this->output
            ->expects(static::atLeastOnce())
            ->method('writeln')
            ->with(static::stringContains('Missing environment variables'));

        $command = $this->createCommand(
            $appUrl,
            $appSecret,
            $environment,
            $projectDir,
        );

        static::assertSame(ManifestGenerateCommand::FAILURE, $command->run($this->input, $this->output));
    }

    public static function provideFailOnAnyParameterMissing(): \Generator
    {
        yield 'appUrl missing' => [null, 'appSecret', 'environment', 'projectDir'];
        yield 'appSecret missing' => ['appUrl', null, 'environment', 'projectDir'];
        yield 'environment missing' => ['appUrl', 'appSecret', null, 'projectDir'];
        yield 'projectDir missing' => ['appUrl', 'appSecret', 'environment', null];

        yield 'appUrl and appSecret missing' => [null, null, 'environment', 'projectDir'];
        yield 'appUrl and environment missing' => [null, 'appSecret', null, 'projectDir'];
        yield 'appUrl and projectDir missing' => [null, 'appSecret', 'environment', null];
        yield 'appSecret and environment missing' => ['appUrl', null, null, 'projectDir'];
        yield 'appSecret and projectDir missing' => ['appUrl', null, 'environment', null];
        yield 'environment and projectDir missing' => ['appUrl', 'appSecret', null, null];

        yield 'only appUrl' => ['appUrl', null, null, null];
        yield 'only appSecret' => [null, 'appSecret', null, null];
        yield 'only environment' => [null, null, 'environment', null];
        yield 'only projectDir' => [null, null, null, 'projectDir'];

        yield 'all missing' => [null, null, null, null];
    }

    public function testRender(): void
    {
        $this->output
            ->expects(static::never())
            ->method('writeln');

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('manifest.xml.twig', [
                'appUrl' => 'appUrl',
                'appSecret' => 'appSecret',
                'isProd' => false,
            ])
            ->willReturn('manifest');

        $command = $this->createCommand();

        $command->expects(static::once())->method('manifestExists')->willReturn(false);
        $command->expects(static::once())->method('writeManifest')->willReturn(true);

        static::assertSame(ManifestGenerateCommand::SUCCESS, $command->run($this->input, $this->output));
    }

    public function testRenderProd(): void
    {
        $this->output
            ->expects(static::never())
            ->method('writeln');

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('manifest.xml.twig', [
                'appUrl' => 'https://braintree.shopware.com',
                'appSecret' => 'appSecret',
                'isProd' => true,
            ])
            ->willReturn('manifest');

        $command = $this->createCommand(environment: 'prod');

        $command->expects(static::once())->method('manifestExists')->willReturn(false);
        $command->expects(static::once())->method('writeManifest')->willReturn(true);

        static::assertSame(ManifestGenerateCommand::SUCCESS, $command->run($this->input, $this->output));
    }

    public function testRenderUnsuccessfulWrite(): void
    {
        $this->output
            ->expects(static::atLeastOnce())
            ->method('writeln');

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->willReturn('manifest');

        $command = $this->createCommand();

        $command->expects(static::once())->method('manifestExists')->willReturn(false);
        $command->expects(static::once())->method('writeManifest')->willReturn(false);

        static::assertSame(ManifestGenerateCommand::FAILURE, $command->run($this->input, $this->output));
    }

    public function testManifestExists(): void
    {
        $this->input
            ->expects(static::exactly(2))
            ->method('getOption')
            ->willReturnCallback(static function (string $option): mixed {
                return match ($option) {
                    'force' => false,
                    'env' => 'dev',
                    default => static::fail('Unexpected option: ' . $option),
                };
            });

        $command = $this->createCommand();

        $command->expects(static::once())->method('manifestExists')->willReturn(true);
        $command->expects(static::never())->method('writeManifest');

        static::assertSame(ManifestGenerateCommand::SUCCESS, $command->run($this->input, $this->output));
    }

    public function testManifestExistsWithForce(): void
    {
        $this->input
            ->expects(static::exactly(2))
            ->method('getOption')
            ->willReturnCallback(static function (string $option): mixed {
                return match ($option) {
                    'force' => true,
                    'env' => 'dev',
                    default => static::fail('Unexpected option: ' . $option),
                };
            });

        $this->output
            ->expects(static::never())
            ->method('writeln');

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->willReturn('manifest');

        $command = $this->createCommand();

        $command->expects(static::once())->method('manifestExists')->willReturn(true);
        $command->expects(static::once())->method('writeManifest')->willReturn(true);

        static::assertSame(ManifestGenerateCommand::SUCCESS, $command->run($this->input, $this->output));
    }

    public function testConfigure(): void
    {
        $command = $this->createCommand();

        static::assertSame('Generate the manifest.xml', $command->getDescription());
        static::assertSame('Generates a manifest.xml from template, which is helpful during development', $command->getHelp());
        static::assertSame('force', $command->getDefinition()->getOption('force')->getName());
    }

    protected function createCommand(
        ?string $appUrl = 'appUrl',
        ?string $appSecret = 'appSecret',
        ?string $environment = 'dev',
        ?string $projectDir = 'projectDir',
    ): ManifestGenerateCommand&MockObject {
        return $this
            ->getMockBuilder(ManifestGenerateCommand::class)
            ->onlyMethods(['manifestExists', 'writeManifest'])
            ->setConstructorArgs([$appUrl, $appSecret, $environment, $projectDir, $this->twig])
            ->getMock();
    }
}
