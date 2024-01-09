<?php declare(strict_types=1);

namespace Swag\Braintree\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Swag\Braintree\Braintree\Gateway\BraintreeConnectionService;
use Swag\Braintree\Braintree\Gateway\Connection\BraintreeConnectionStatus;
use Swag\Braintree\Entity\ConfigEntity;
use Swag\Braintree\Entity\CurrencyMappingEntity;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Framework\ArgumentResolver\Dto\Criteria;
use Swag\Braintree\Repository\ConfigRepository;
use Swag\Braintree\Repository\CurrencyMappingRepository;
use Swag\Braintree\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(path: '/api')]
class EntityController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ShopRepository $shopRepository,
        private readonly ConfigRepository $configRepository,
        private readonly CurrencyMappingRepository $currencyMappingRepository,
        private readonly BraintreeConnectionService $connectionService,
    ) {
    }

    #[Route(path: '/entity/shop', name: 'entity.shop.get', methods: [Request::METHOD_GET])]
    public function getShopEntity(ShopEntity $shop): ShopEntity
    {
        return $shop;
    }

    #[Route(path: '/entity/shop', name: 'entity.shop.update', methods: [Request::METHOD_PATCH])]
    public function updateShopEntity(Request $request, ShopEntity $shop): BraintreeConnectionStatus
    {
        /** @var ShopEntity $shop */
        $shop = $this->shopRepository->deserializeInto($shop, $request->getContent());

        $connectionService = $this->connectionService->fromShop($shop);

        $this->entityManager->persist($shop);
        $this->entityManager->flush();

        return $connectionService->testConnection();
    }

    /**
     * @return ConfigEntity[]
     */
    #[Route(path: '/entity/config', name: 'entity.config.get', methods: [Request::METHOD_POST])]
    public function getConfigEntities(Criteria $criteria, ShopEntity $shop): array
    {
        if (\count($criteria->ids) > 0) {
            return $this->configRepository->findBy(['shop' => $shop, 'id' => $criteria->ids]);
        }

        return $shop->getConfigs()->toArray();
    }

    #[Route(path: '/entity/by-sales-channel/config/{salesChannelId}', name: 'entity.by-sales-channel.config.get', methods: [Request::METHOD_GET])]
    public function getBySalesChannelConfigEntity(string $salesChannelId, ShopEntity $shop): ?ConfigEntity
    {
        if ($salesChannelId === 'null') {
            $salesChannelId = null;
        }

        return $this->configRepository
            ->findOneBy(['shop' => $shop, 'salesChannelId' => $salesChannelId]) ?? (new ConfigEntity())
            ->setSalesChannelId($salesChannelId)
            ->setShop($shop);
    }

    #[Route(path: '/entity/config', name: 'entity.config.upsert', methods: [Request::METHOD_PATCH])]
    public function upsertConfigEntities(Request $request, ShopEntity $shop): void
    {
        $configs = \json_decode($request->getContent(), true, \JSON_THROW_ON_ERROR);

        $this->configRepository->upsert($configs, $shop);
    }

    /**
     * @return CurrencyMappingEntity[]
     */
    #[Route(path: '/entity/currency_mapping', name: 'entity.currency_mapping.get', methods: [Request::METHOD_POST])]
    public function getCurrencyMappingEntities(Criteria $criteria, ShopEntity $shop): array
    {
        if (\count($criteria->ids) > 0) {
            return $this->currencyMappingRepository->findBy(['shop' => $shop, 'id' => $criteria->ids]);
        }

        return $shop->getCurrencyMappings()->toArray();
    }

    /**
     * @return CurrencyMappingEntity[]
     */
    #[Route(path: '/entity/by-sales-channel/currency_mapping/{salesChannelId}', name: 'entity.by-sales-channel.currency_mapping.get', methods: [Request::METHOD_GET])]
    public function getBySalesChannelCurrencyMappingEntities(string $salesChannelId, ShopEntity $shop): array
    {
        if ($salesChannelId === 'null') {
            $salesChannelId = null;
        }

        return $this->currencyMappingRepository->findBy(['shop' => $shop, 'salesChannelId' => $salesChannelId]);
    }

    #[Route(path: '/entity/currency_mapping', name: 'entity.currency_mapping.upsert', methods: [Request::METHOD_PATCH])]
    public function upsertBySalesChannelCurrencyMappingEntities(Request $request, ShopEntity $shop): void
    {
        $currencyMappings = \json_decode($request->getContent(), true, \JSON_THROW_ON_ERROR);

        $this->currencyMappingRepository->delete($currencyMappings['deleted']);
        $this->currencyMappingRepository->upsert($currencyMappings['upsert'], $shop);
    }
}
