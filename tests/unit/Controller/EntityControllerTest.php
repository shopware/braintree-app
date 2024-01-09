<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Gateway\BraintreeConnectionService;
use Swag\Braintree\Braintree\Gateway\Connection\BraintreeConnectionStatus;
use Swag\Braintree\Controller\EntityController;
use Swag\Braintree\Entity\ConfigEntity;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Framework\ArgumentResolver\Dto\Criteria;
use Swag\Braintree\Repository\ConfigRepository;
use Swag\Braintree\Repository\CurrencyMappingRepository;
use Swag\Braintree\Repository\ShopRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

#[CoversClass(EntityController::class)]
class EntityControllerTest extends TestCase
{
    private MockObject&EntityManagerInterface $entityManager;

    private MockObject&ShopRepository $shopRepository;

    private MockObject&ConfigRepository $configRepository;

    private MockObject&CurrencyMappingRepository $currencyMappingRepository;

    private MockObject&BraintreeConnectionService $connectionService;

    private EntityController $entityController;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->shopRepository = $this->createMock(ShopRepository::class);
        $this->configRepository = $this->createMock(ConfigRepository::class);
        $this->currencyMappingRepository = $this->createMock(CurrencyMappingRepository::class);
        $this->connectionService = $this->createMock(BraintreeConnectionService::class);

        $this->entityController = new EntityController($this->entityManager, $this->shopRepository, $this->configRepository, $this->currencyMappingRepository, $this->connectionService);
    }

    public function testGetShopEntity(): void
    {
        $shop = new ShopEntity('', '', '');

        static::assertSame($shop, $this->entityController->getShopEntity($shop));
    }

    public function testUpdateShopEntity(): void
    {
        $request = new Request();
        $shop = new ShopEntity('', '', '');

        $this->shopRepository
            ->expects(static::once())
            ->method('deserializeInto')
            ->with($shop, $request->getContent())
            ->willReturn($shop);

        $this->connectionService
            ->expects(static::once())
            ->method('fromShop')
            ->willReturn($this->connectionService);

        $this->connectionService
            ->expects(static::once())
            ->method('testConnection')
            ->willReturn(BraintreeConnectionStatus::connected());

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with($shop);

        $this->entityManager
            ->expects(static::once())
            ->method('flush');

        $this->entityController->updateShopEntity($request, $shop);
    }

    public function testGetBySalesChannelConfigEntity(): void
    {
        $shop = $this->createMock(ShopEntity::class);
        $config = new ConfigEntity();

        $this->configRepository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['shop' => $shop, 'salesChannelId' => 'this-is-sales-channel-id'])
            ->willReturn($config);

        $foundConfig = $this->entityController->getBySalesChannelConfigEntity('this-is-sales-channel-id', $shop);
        static::assertSame($config, $foundConfig);
    }

    public function testGetBySalesChannelConfigEntityFoundNull(): void
    {
        $shop = $this->createMock(ShopEntity::class);

        $this->configRepository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['shop' => $shop, 'salesChannelId' => 'this-is-sales-channel-id'])
            ->willReturn(null);

        $foundConfig = $this->entityController->getBySalesChannelConfigEntity('this-is-sales-channel-id', $shop);
        static::assertNull($foundConfig->getId());
        static::assertSame('this-is-sales-channel-id', $foundConfig->getSalesChannelId());
        static::assertSame($shop, $foundConfig->getShop());
    }

    public function testGetBySalesChannelConfigEntityWithSalesChannelIdNull(): void
    {
        $shop = $this->createMock(ShopEntity::class);

        $this->configRepository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(['shop' => $shop, 'salesChannelId' => null])
            ->willReturn(null);

        $foundConfig = $this->entityController->getBySalesChannelConfigEntity('null', $shop);
        static::assertNull($foundConfig->getSalesChannelId());
    }

    public function testGetConfigEntitiesWithIds(): void
    {
        $criteria = new Criteria();
        $criteria->ids = [Uuid::v7()];

        $shop = $this->createMock(ShopEntity::class);
        $shop->expects(static::never())->method('getConfigs');

        $this->configRepository
            ->expects(static::once())
            ->method('findBy')
            ->with(['shop' => $shop, 'id' => $criteria->ids])
            ->willReturn([]);

        static::assertSame([], $this->entityController->getConfigEntities($criteria, $shop));
    }

    public function testGetConfigEntitiesWithoutIds(): void
    {
        $criteria = new Criteria();

        $shop = $this->createMock(ShopEntity::class);
        $shop
            ->expects(static::once())
            ->method('getConfigs')
            ->willReturn(new ArrayCollection());

        $this->configRepository
            ->expects(static::never())
            ->method('findBy');

        static::assertSame([], $this->entityController->getConfigEntities($criteria, $shop));
    }

    public function testGetCurrencyMappingEntitiesWithIds(): void
    {
        $criteria = new Criteria();
        $criteria->ids = [Uuid::v7()];

        $shop = $this->createMock(ShopEntity::class);
        $shop->expects(static::never())->method('getCurrencyMappings');

        $this->currencyMappingRepository
            ->expects(static::once())
            ->method('findBy')
            ->with(['shop' => $shop, 'id' => $criteria->ids])
            ->willReturn([]);

        static::assertSame([], $this->entityController->getCurrencyMappingEntities($criteria, $shop));
    }

    public function testGetCurrencyMappingEntitiesWithoutIds(): void
    {
        $criteria = new Criteria();

        $shop = $this->createMock(ShopEntity::class);
        $shop
            ->expects(static::once())
            ->method('getCurrencyMappings')
            ->willReturn(new ArrayCollection());

        $this->currencyMappingRepository
            ->expects(static::never())
            ->method('findBy');

        static::assertSame([], $this->entityController->getCurrencyMappingEntities($criteria, $shop));
    }

    public function testGetBySalesChannelCurrencyMappingEntities(): void
    {
        $shop = $this->createMock(ShopEntity::class);

        $this->currencyMappingRepository
            ->expects(static::once())
            ->method('findBy')
            ->with(['shop' => $shop, 'salesChannelId' => 'this-is-sales-channel-id'])
            ->willReturn([]);

        $this->entityController->getBySalesChannelCurrencyMappingEntities('this-is-sales-channel-id', $shop);
    }

    public function testGetBySalesChannelCurrencyMappingEntitiesWithSalesChannelIdNull(): void
    {
        $shop = $this->createMock(ShopEntity::class);

        $this->currencyMappingRepository
            ->expects(static::once())
            ->method('findBy')
            ->with(['shop' => $shop, 'salesChannelId' => null])
            ->willReturn([]);

        $this->entityController->getBySalesChannelCurrencyMappingEntities('null', $shop);
    }

    public function testUpsertConfigEntities(): void
    {
        $data = ['this-is-key' => 'this-is-value'];
        $shop = new ShopEntity('', '', '');

        $request = new Request(content: \json_encode($data));

        $this->configRepository
            ->expects(static::once())
            ->method('upsert')
            ->with($data, $shop);

        $this->entityController->upsertConfigEntities($request, $shop);
    }

    public function testUpsertBySalesChannelCurrencyMappingEntities(): void
    {
        $data = [
            'deleted' => ['this-is-id'],
            'upsert' => ['this-is-key' => 'this-is-value'],
        ];
        $shop = new ShopEntity('', '', '');

        $request = new Request(content: \json_encode($data));

        $this->currencyMappingRepository
            ->expects(static::once())
            ->method('delete')
            ->with($data['deleted']);

        $this->currencyMappingRepository
            ->expects(static::once())
            ->method('upsert')
            ->with($data['upsert'], $shop);

        $this->entityController->upsertBySalesChannelCurrencyMappingEntities($request, $shop);
    }
}
