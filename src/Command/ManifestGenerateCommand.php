<?php declare(strict_types=1);

namespace Swag\Braintree\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

#[AsCommand(name: 'manifest:generate')]
class ManifestGenerateCommand extends Command
{
    public function __construct(
        #[Autowire(env: 'APP_URL')]
        private readonly ?string $appUrl,
        #[Autowire(env: 'APP_SECRET')]
        private readonly ?string $appSecret,
        #[Autowire(param: 'kernel.environment')]
        private readonly ?string $environment,
        #[Autowire(param: 'kernel.project_dir')]
        private readonly ?string $projectDir,
        #[Autowire(service: 'twig')]
        private readonly Environment $twig,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->appUrl === null || $this->appSecret === null || $this->environment === null || $this->projectDir === null) {
            $io->error('Missing environment variables');

            return Command::FAILURE;
        }

        $manifest = $this->twig->render('manifest.xml.twig', [
            'appUrl' => $this->environment === 'prod' ? 'https://braintree.shopware.com' : $this->appUrl,
            'appSecret' => $this->appSecret,
            'isProd' => $this->environment === 'prod',
        ]);

        $write = true;

        if ($this->manifestExists() && !$input->getOption('force')) {
            $write = $io->confirm('manifest.xml already exists. Do you want to overwrite it?', false);
        }

        if ($write) {
            $success = $this->writeManifest($manifest);

            if ($success === false) {
                $io->error('Could not write manifest.xml');

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->setDescription('Generate the manifest.xml');
        $this->addOption('force', 'f', null, 'Force overwrite');
    }

    protected function manifestExists(): bool
    {
        return \file_exists($this->projectDir . '/manifest.xml');
    }

    protected function writeManifest(string $manifest): bool
    {
        return \file_put_contents($this->projectDir . '/manifest.xml', $manifest) !== false;
    }
}
