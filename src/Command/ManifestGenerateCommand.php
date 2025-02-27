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
    public const BASE_URL_PROD = 'https://braintree.shopware.com';

    public function __construct(
        #[Autowire(env: 'APP_URL')]
        protected readonly ?string $appUrl,
        #[Autowire(env: 'APP_SECRET')]
        protected readonly ?string $appSecret,
        #[Autowire(param: 'kernel.environment')]
        protected readonly ?string $environment,
        #[Autowire(param: 'app_manifest')]
        protected readonly ?string $appManifest,
        #[Autowire(service: 'twig')]
        protected readonly Environment $twig,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (\in_array(null, [$this->appUrl, $this->appSecret, $this->environment, $this->appManifest], true)) {
            $io->error('Missing environment variables');

            return Command::FAILURE;
        }

        $isProd = ($input->getOption('env') ?? $this->environment) === 'prod';

        $manifest = $this->twig->render('manifest.xml.twig', [
            'appUrl' => $isProd ? self::BASE_URL_PROD : $this->appUrl,
            'appSecret' => $this->appSecret,
            'isProd' => $isProd,
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
        $this->setHelp('Generates a manifest.xml from template, which is helpful during development');
        $this->addOption('force', 'f', null, 'Force overwrite');
    }

    /**
     * @infection-ignore-all - not testable
     */
    protected function manifestExists(): bool
    {
        return \file_exists($this->appManifest);
    }

    /**
     * @infection-ignore-all - not testable
     */
    protected function writeManifest(string $manifest): bool
    {
        return \file_put_contents($this->appManifest, $manifest) !== false;
    }
}
