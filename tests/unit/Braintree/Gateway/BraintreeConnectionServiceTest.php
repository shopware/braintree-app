<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Braintree\Gateway;

use Braintree\Exception as BraintreeException;
use Braintree\Gateway;
use Braintree\MerchantAccount;
use Braintree\MerchantAccountGateway;
use Braintree\PaginatedCollection;
use Braintree\PaginatedResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Gateway\BraintreeConnectionService;
use Swag\Braintree\Braintree\Gateway\Connection\BraintreeConnectionStatus;
use Swag\Braintree\Entity\ShopEntity;

#[CoversClass(BraintreeConnectionService::class)]
class BraintreeConnectionServiceTest extends TestCase
{
    public function testFromShop(): void
    {
        $gateway = $this->createMock(Gateway::class);
        $service = new BraintreeConnectionService($gateway);

        $shop = (new ShopEntity('', '', ''))
            ->setBraintreeMerchantId('this-is-merchant-id')
            ->setBraintreePrivateKey('this-is-private-key')
            ->setBraintreePublicKey('this-is-public-key');

        $service = $service->fromShop($shop);

        $gatewayProperty = new \ReflectionProperty($service::class, 'gateway');
        $gatewayProperty->setAccessible(true);

        /** @var Gateway $gateway */
        $gateway = $gatewayProperty->getValue($service);
        $config = $gateway->config;

        static::assertNotNull($config);
        static::assertSame('production', $config->getEnvironment());
        static::assertSame($shop->getBraintreeMerchantId(), $config->getMerchantId());
        static::assertSame($shop->getBraintreePrivateKey(), $config->getPrivateKey());
        static::assertSame($shop->getBraintreePublicKey(), $config->getPublicKey());
    }

    public function testTestConnection(): void
    {
        $account = MerchantAccount::factory(['default' => true]);

        $merchantGateway = $this->createMock(MerchantAccountGateway::class);
        $merchantGateway
            ->expects(static::once())
            ->method('all')
            ->willReturn(new PaginatedCollection(
                [
                    'object' => $this->getPager([$account]),
                    'method' => 'all',
                    'query' => [],
                ]
            ));

        $gateway = $this->createMock(Gateway::class);
        $gateway
            ->expects(static::exactly(1))
            ->method('merchantAccount')
            ->willReturn($merchantGateway);

        $service = new BraintreeConnectionService($gateway);
        $status = $service->testConnection();

        static::assertSame(BraintreeConnectionStatus::STATUS_CONNECTED, $status->connectionStatus);
        static::assertSame($account, $status->merchantAccount);
    }

    public function testTestConnectionWithException(): void
    {
        $merchantGateway = $this->createMock(MerchantAccountGateway::class);
        $merchantGateway
            ->expects(static::once())
            ->method('all')
            ->willReturn(new PaginatedCollection(
                [
                    'object' => $this->getPager(),
                    'method' => 'all',
                    'query' => [],
                ]
            ));

        $gateway = $this->createMock(Gateway::class);
        $gateway
            ->expects(static::exactly(1))
            ->method('merchantAccount')
            ->willReturn($merchantGateway);

        $service = new BraintreeConnectionService($gateway);
        $status = $service->testConnection();

        static::assertSame(BraintreeConnectionStatus::STATUS_DISCONNECTED, $status->connectionStatus);
        static::assertNull($status->merchantAccount);
    }

    public function testGetDefaultMerchant(): void
    {
        $accounts = [
            MerchantAccount::factory(['id' => 'id1', 'default' => false]),
            MerchantAccount::factory(['id' => 'id2', 'default' => true]),
            MerchantAccount::factory(['id' => 'id3', 'default' => false]),
        ];

        $collection = new PaginatedCollection(
            [
                'object' => $this->getPager($accounts),
                'method' => 'all',
                'query' => [],
            ],
        );

        static::assertTrue($collection->valid());

        $accountGateway = $this->createMock(MerchantAccountGateway::class);
        $accountGateway
            ->expects(static::once())
            ->method('all')
            ->willReturn($collection);

        $gateway = $this->createMock(Gateway::class);
        $gateway
            ->expects(static::once())
            ->method('merchantAccount')
            ->willReturn($accountGateway);

        $service = new BraintreeConnectionService($gateway);
        $merchant = $service->getDefaultMerchantAccount();

        static::assertSame('id2', $merchant->id);
    }

    public function testGetDefaultMerchantWithoutDefault(): void
    {
        $accounts = [
            MerchantAccount::factory(['id' => 'id1', 'default' => false]),
            MerchantAccount::factory(['id' => 'id2', 'default' => false]),
            MerchantAccount::factory(['id' => 'id3', 'default' => false]),
        ];

        $collection = new PaginatedCollection(
            [
                'object' => $this->getPager($accounts),
                'method' => 'all',
                'query' => [],
            ],
        );

        static::assertTrue($collection->valid());

        $accountGateway = $this->createMock(MerchantAccountGateway::class);
        $accountGateway
            ->expects(static::once())
            ->method('all')
            ->willReturn($collection);

        $gateway = $this->createMock(Gateway::class);
        $gateway
            ->expects(static::once())
            ->method('merchantAccount')
            ->willReturn($accountGateway);

        $service = new BraintreeConnectionService($gateway);
        $merchant = $service->getDefaultMerchantAccount();

        static::assertNull($merchant);
    }

    public function testGetDefaultMerchantWithException(): void
    {
        $accounts = [
            MerchantAccount::factory(['id' => 'id1', 'default' => false]),
            MerchantAccount::factory(['id' => 'id2', 'default' => false]),
            MerchantAccount::factory(['id' => 'id3', 'default' => false]),
        ];

        $collection = new PaginatedCollection(
            [
                'object' => $this->getPager($accounts),
                'method' => 'all',
                'query' => [],
            ],
        );

        static::assertTrue($collection->valid());

        $accountGateway = $this->createMock(MerchantAccountGateway::class);
        $accountGateway
            ->expects(static::once())
            ->method('all')
            ->willThrowException(new BraintreeException());

        $gateway = $this->createMock(Gateway::class);
        $gateway
            ->expects(static::once())
            ->method('merchantAccount')
            ->willReturn($accountGateway);

        $service = new BraintreeConnectionService($gateway);
        $merchant = $service->getDefaultMerchantAccount();

        static::assertNull($merchant);
    }

    public function testGetAllMerchantAccounts(): void
    {
        $accounts = [
            MerchantAccount::factory(['id' => 'id1', 'default' => false]),
            MerchantAccount::factory(['id' => 'id2', 'default' => false]),
            MerchantAccount::factory(['id' => 'id3', 'default' => false]),
        ];

        $collection = new PaginatedCollection(
            [
                'object' => $this->getPager($accounts),
                'method' => 'all',
                'query' => [],
            ],
        );

        static::assertTrue($collection->valid());

        $accountGateway = $this->createMock(MerchantAccountGateway::class);
        $accountGateway
            ->expects(static::once())
            ->method('all')
            ->willReturn($collection);

        $gateway = $this->createMock(Gateway::class);
        $gateway
            ->expects(static::once())
            ->method('merchantAccount')
            ->willReturn($accountGateway);

        $service = new BraintreeConnectionService($gateway);
        $merchantAccounts = $service->getAllMerchantAccounts();

        static::assertCount(3, $merchantAccounts);
    }

    /**
     * @param object[] $items
     */
    private function getPager(array $items = []): object
    {
        return new class($items) {
            /**
             * @param object[] $items
             */
            public function __construct(private readonly array $items)
            {
            }

            /**
             * @param mixed[] $query
             */
            public function all(array $query): PaginatedResult
            {
                return new PaginatedResult(\count($this->items), \count($this->items), $this->items);
            }
        };
    }
}
