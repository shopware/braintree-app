<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Braintree\Util;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Util\ReportService;
use Swag\Braintree\Entity\TransactionReportEntity;
use Swag\Braintree\Repository\TransactionReportRepository;

#[CoversClass(ReportService::class)]
class ReportServiceTest extends TestCase
{
    private MockObject&EntityManagerInterface $entityManager;

    private MockObject&TransactionReportRepository $transactionReportRepository;

    private ReportService $service;

    /**
     * @var array<int, array{request: Request, response: Response}>
     */
    private array $clientHistory;

    private MockHandler $clientHandler;

    protected function setUp(): void
    {
        $this->transactionReportRepository = $this->createMock(TransactionReportRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->clientHistory = [];
        $this->clientHandler = new MockHandler();
        $clientHandlerStack = HandlerStack::create($this->clientHandler);
        $clientHandlerStack->push(Middleware::history($this->clientHistory));

        $this->service = new ReportService(
            $this->entityManager,
            $this->transactionReportRepository,
            'test-id',
            new Client(['handler' => $clientHandlerStack]),
        );
    }

    public function testSendTurnoverReports(): void
    {
        $this->clientHandler->append(new Response(), new Response(), new Response());

        $reports = [
            (new TransactionReportEntity())
                ->setCurrencyIso('EUR')
                ->setTotalPrice('10.52'),

            (new TransactionReportEntity())
                ->setCurrencyIso('EUR')
                ->setTotalPrice('9.48'),

            (new TransactionReportEntity())
                ->setCurrencyIso('GBP')
                ->setTotalPrice('100.00'),
        ];

        $this->transactionReportRepository
            ->expects(static::once())
            ->method('findAll')
            ->willReturn($reports);

        $this->entityManager
            ->expects(static::once())
            ->method('flush');

        $this->entityManager
            ->expects(static::exactly(3))
            ->method('remove');

        $this->service->sendTurnoverReports();

        static::assertEquals([[
            'reportDataKeys' => ['turnover' => 20],
            'currency' => 'EUR',
        ], [
            'reportDataKeys' => ['turnover' => 100],
            'currency' => 'GBP',
        ]], $this->extractTurnoverReports($this->clientHistory));
    }

    public function testSendTurnoverReportsWithRejectedReport(): void
    {
        $this->clientHandler->append(new Response(400), new Response());

        $successfulReport = (new TransactionReportEntity())
            ->setCurrencyIso('GBP')
            ->setTotalPrice('100.00');

        $reports = [
            (new TransactionReportEntity())
                ->setCurrencyIso('EUR')
                ->setTotalPrice('10.52'),

            (new TransactionReportEntity())
                ->setCurrencyIso('EUR')
                ->setTotalPrice('9.48'),

            $successfulReport,
        ];

        $this->transactionReportRepository
            ->expects(static::once())
            ->method('findAll')
            ->willReturn($reports);

        $this->entityManager
            ->expects(static::once())
            ->method('flush');

        // will only remove the successful reported transaction
        $this->entityManager
            ->expects(static::once())
            ->method('remove')
            ->with($successfulReport);

        $rejectedCurrencies = $this->service->sendTurnoverReports();

        static::assertEquals(['EUR'], $rejectedCurrencies);

        static::assertEquals([[
            'reportDataKeys' => ['turnover' => 20],
            'currency' => 'EUR',
        ], [
            'reportDataKeys' => ['turnover' => 100],
            'currency' => 'GBP',
        ]], $this->extractTurnoverReports($this->clientHistory));
    }

    public function testMultipleRejectedCurrencies(): void
    {
        $this->clientHandler->append(new Response(400), new Response(), new Response(400));

        $successfulReport = (new TransactionReportEntity())
            ->setCurrencyIso('CHF')
            ->setTotalPrice('9.48');

        $reports = [
            (new TransactionReportEntity())
                ->setCurrencyIso('EUR')
                ->setTotalPrice('10.52'),

            $successfulReport,

            (new TransactionReportEntity())
                ->setCurrencyIso('GBP')
                ->setTotalPrice('100.00'),
        ];

        $this->transactionReportRepository
            ->expects(static::once())
            ->method('findAll')
            ->willReturn($reports);

        $this->entityManager
            ->expects(static::once())
            ->method('flush');

        // will only remove the successful reported transaction
        $this->entityManager
            ->expects(static::once())
            ->method('remove')
            ->with($successfulReport);

        $rejectedCurrencies = $this->service->sendTurnoverReports();

        static::assertEquals(['EUR', 'GBP'], $rejectedCurrencies);

        static::assertEquals([[
            'reportDataKeys' => ['turnover' => 10.52],
            'currency' => 'EUR',
        ], [
            'reportDataKeys' => ['turnover' => 9.48],
            'currency' => 'CHF',
        ], [
            'reportDataKeys' => ['turnover' => 100],
            'currency' => 'GBP',
        ]], $this->extractTurnoverReports($this->clientHistory));
    }

    public function testTransactionFloatCasting(): void
    {
        $this->clientHandler->append(new Response());

        $reports = [
            (new TransactionReportEntity())
                ->setCurrencyIso('EUR')
                ->setTotalPrice('10.52s'),
        ];

        $this->transactionReportRepository
            ->expects(static::once())
            ->method('findAll')
            ->willReturn($reports);

        $this->service->sendTurnoverReports();

        static::assertNotNull($this->clientHandler->getLastRequest());

        $requestBody = $this->clientHandler->getLastRequest()->getBody()->getContents();

        static::assertJson($requestBody);

        $requestBodyArray = \json_decode($requestBody, true);

        static::assertIsArray($requestBodyArray);

        static::assertArrayHasKey('reportDataKeys', $requestBodyArray);
        static::assertIsArray($requestBodyArray['reportDataKeys']);

        static::assertArrayHasKey('turnover', $requestBodyArray['reportDataKeys']);
        static::assertIsFloat($requestBodyArray['reportDataKeys']['turnover']);
    }

    public function testReportDateIsSent(): void
    {
        $this->clientHandler->append(new Response());

        $reports = [
            (new TransactionReportEntity())
                ->setCurrencyIso('EUR')
                ->setTotalPrice('10.52s'),
        ];

        $this->transactionReportRepository
            ->expects(static::once())
            ->method('findAll')
            ->willReturn($reports);

        $this->service->sendTurnoverReports();

        static::assertNotNull($this->clientHandler->getLastRequest());

        $requestBody = $this->clientHandler->getLastRequest()->getBody()->getContents();

        static::assertJson($requestBody);

        $requestBodyArray = \json_decode($requestBody, true);

        static::assertArrayHasKey('reportDate', $requestBodyArray);
        static::assertIsString($requestBodyArray['reportDate']);

        $reportDate = \DateTime::createFromFormat(\DateTimeInterface::ATOM, $requestBodyArray['reportDate']);

        static::assertInstanceOf(\DateTimeInterface::class, $reportDate);
    }

    public function testApiIdentifierIsSent(): void
    {
        $this->clientHandler->append(new Response());

        $reports = [
            (new TransactionReportEntity())
                ->setCurrencyIso('EUR')
                ->setTotalPrice('10.52s'),
        ];

        $this->transactionReportRepository
            ->expects(static::once())
            ->method('findAll')
            ->willReturn($reports);

        $this->service->sendTurnoverReports();

        static::assertNotNull($this->clientHandler->getLastRequest());

        $requestBody = $this->clientHandler->getLastRequest()->getBody()->getContents();

        static::assertJson($requestBody);

        $requestBodyArray = \json_decode($requestBody, true);

        static::assertArrayHasKey('identifier', $requestBodyArray);
        static::assertSame('test-id', $requestBodyArray['identifier']);
    }

    /**
     * Extracts all turnover reports, successful and failed ones
     *
     * @param array<int, array{request: Request, response: Response}> $history
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractTurnoverReports(array $history): array
    {
        return \array_map(
            function (array $entry) {
                $body = \json_decode($entry['request']->getBody()->getContents(), true);
                static::assertIsArray($body);

                unset($body['identifier']);
                unset($body['reportDate']);

                return $body;
            },
            $history,
        );
    }
}
