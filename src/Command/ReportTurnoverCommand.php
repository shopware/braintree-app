<?php declare(strict_types=1);

namespace Swag\Braintree\Command;

use Swag\Braintree\Braintree\Util\ReportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'report:turnover')]
class ReportTurnoverCommand extends Command
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $rejectedCurrencies = $this->reportService->sendTurnoverReports();

        if (\count($rejectedCurrencies) > 0) {
            $io->warning(\sprintf(
                'The following currencies could not be reported: %s',
                \implode(', ', $rejectedCurrencies)
            ));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->setDescription('Report the turnover of a given time period');
    }
}
