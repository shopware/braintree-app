<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Braintree\Gateway\BraintreeConnectionService;
use Swag\Braintree\Braintree\Gateway\Connection\BraintreeConnectionStatus;
use Swag\Braintree\Controller\BraintreeConfigurationController;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Repository\ShopRepository;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(BraintreeConfigurationController::class)]
class BraintreeConfigurationControllerTest extends TestCase
{
    private BraintreeConfigurationController $controller;

    private MockObject&BraintreeConnectionService $connectionService;

    private MockObject&EntityManagerInterface $entityManager;

    private MockObject&ShopRepository $shopRepository;

    protected function setUp(): void
    {
        $this->connectionService = $this->createMock(BraintreeConnectionService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->shopRepository = $this->createMock(ShopRepository::class);

        $this->controller = new BraintreeConfigurationController(
            $this->connectionService,
            $this->entityManager,
            $this->shopRepository,
        );
    }

    public function testGetMerchantAccounts(): void
    {
        $this->connectionService
            ->expects(static::once())
            ->method('getAllMerchantAccounts')
            ->willReturn([]);

        $this->controller->getMerchantAccounts();
    }

    public function testGetDefaultMerchantAccount(): void
    {
        $this->connectionService
            ->expects(static::once())
            ->method('getDefaultMerchantAccount')
            ->willReturn(null);

        $this->controller->getDefaultMerchantAccount();
    }

    public function testConfigStatus(): void
    {
        $this->connectionService
            ->expects(static::once())
            ->method('testConnection')
            ->willReturn(BraintreeConnectionStatus::connected());

        $this->controller->configStatus();
    }

    public function testConfigTest(): void
    {
        $shop = new ShopEntity('', '', '');
        $request = new Request();

        $this->shopRepository
            ->expects(static::once())
            ->method('deserializeInto')
            ->with($shop, $request->getContent())
            ->willReturn($shop);

        $this->connectionService
            ->expects(static::once())
            ->method('fromShop')
            ->with($shop)
            ->willReturn($this->connectionService);

        $this->connectionService
            ->expects(static::once())
            ->method('testConnection')
            ->willReturn(BraintreeConnectionStatus::connected());

        $this->controller->configTest($request, $shop);
    }

    public function testConfigDisconnect(): void
    {
        $shop = $this->createMock(ShopEntity::class);

        $shop
            ->expects(static::once())
            ->method('reset');

        $this->entityManager
            ->expects(static::once())
            ->method('persist')
            ->with($shop);

        $this->entityManager
            ->expects(static::once())
            ->method('flush');

        $this->controller->configDisconnect($shop);
    }
}
