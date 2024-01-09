<?php declare(strict_types=1);

namespace Swag\Braintree\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'setup:url')]
class SetupUrlCommand extends Command
{
    // relative to %kernel.project_dir%
    public const FILES = [
        'manifest.xml',
        'assets/src/service/api.ts',
        'Resources/app/storefront/{src/checkout,dist/storefront/js/swag-braintree-app}/*',
    ];

    public function __construct(
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $appPath,
        #[Autowire(env: 'APP_URL')]
        private readonly string $appUrl,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $fromUrl = $input->getOption('from-url') ?? 'https://realdomain.example.com';
        $toUrl = $input->getOption('to-url') ?? $this->appUrl;

        if (!$fromUrl || !$toUrl) {
            $io->error('Seams pointless to replace from/with nothing');

            return Command::INVALID;
        }

        if ($fromUrl === $toUrl) {
            $io->warning(sprintf('Isn\'t it pointless to replace "%s" with itself?', $toUrl));

            return Command::FAILURE;
        }

        $io->title(\sprintf('Replacing %s with %s', $fromUrl, $toUrl));

        foreach (self::FILES as $file) {
            foreach (\glob($this->appPath . '/' . $file, \GLOB_BRACE) as $file) {
                if (!\is_file($file)) {
                    $io->text(\sprintf('Skipping non-file %s', \substr($file, \strlen($this->appPath) + 1)));

                    continue;
                }

                $io->text(\sprintf('Replacing in %s', \substr($file, \strlen($this->appPath) + 1)));

                $content = \file_get_contents($file);
                $content = \str_replace($fromUrl, $toUrl, $content);
                \file_put_contents($file, $content);
            }
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Replaces hardcoded urls app urls with yours (Default: $APP_URL)')
            ->addOption('from-url', mode: InputArgument::OPTIONAL, description: 'URL to replace with')
            ->addOption('to-url', mode: InputArgument::OPTIONAL, description: 'URL to replace');
    }
}
