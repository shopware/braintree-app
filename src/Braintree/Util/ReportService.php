<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Util;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\RequestOptions;
use Swag\Braintree\Repository\TransactionReportRepository;

class ReportService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TransactionReportRepository $transactionReportRepository,
        private readonly ?string $apiIdentifier,
        private readonly Client $client,
    ) {
    }

    /**
     * @return array<string> List of currencies that could not be reported
     */
    public function sendTurnoverReports(): array
    {
        $transactions = $this->transactionReportRepository->findAll();

        $reports = [];
        foreach ($transactions as $transaction) {
            $reports[$transaction->getCurrencyIso()] ??= 0;
            $reports[$transaction->getCurrencyIso()] += (float) $transaction->getTotalPrice();
        }

        $requests = [];
        foreach ($reports as $currency => $turnover) {
            $body = [
                'identifier' => $this->apiIdentifier ?? '',
                'reportDate' => (new \DateTime())->format(\DateTimeInterface::ATOM),
                'reportDataKeys' => ['turnover' => $turnover],
                'currency' => $currency,
            ];

            $requests[$currency] = $this->client->postAsync(
                '/shopwarepartners/reports/technology',
                [RequestOptions::JSON => $body],
            );
        }

        $rejectedCurrencies = [];
        /** @var array{state: string, reason: ClientException} $response */
        foreach (Utils::settle($requests)->wait() as $currency => $response) {
            if ($response['state'] !== Promise::REJECTED) {
                continue;
            }

            // @TODO - Implement logging
            // $this->logger->warning(\sprintf(
            //     'Failed to report turnover for "%s": %s',
            //     $currency,
            //     $response['reason']->getMessage()
            // ));

            $rejectedCurrencies[] = $currency;
        }

        foreach ($transactions as $transaction) {
            // transaction has a rejected currency and shouldn't be deleted
            if (\in_array($transaction->getCurrencyIso(), $rejectedCurrencies, true)) {
                continue;
            }

            $this->em->remove($transaction);
        }

        $this->em->flush();

        return $rejectedCurrencies;
    }
}
