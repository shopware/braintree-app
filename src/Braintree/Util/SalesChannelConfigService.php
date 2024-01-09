<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Util;

use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Repository\ConfigRepository;
use Swag\Braintree\Repository\CurrencyMappingRepository;

class SalesChannelConfigService
{
    public function __construct(
        private readonly CurrencyMappingRepository $currencyMappingRepository,
        private readonly ConfigRepository $configRepository,
    ) {
    }

    public function getMerchantId(string $salesChannelId, string $currencyId, ShopInterface $shop): ?string
    {
        $currencyMappings = $this->currencyMappingRepository->findBy(['salesChannelId' => [null, $salesChannelId], 'currencyId' => $currencyId, 'shop' => $shop]);

        foreach ($currencyMappings as $currencyMapping) {
            $currencyMappings[$currencyMapping->getSalesChannelId()] = $currencyMapping;
        }

        if (isset($currencyMappings[$salesChannelId])) {
            return $currencyMappings[$salesChannelId]->getMerchantAccountId();
        }

        if (isset($currencyMappings[null])) {
            return $currencyMappings[null]->getMerchantAccountId();
        }

        return null;
    }

    public function isThreeDSecureEnforced(string $salesChannelId, ShopInterface $shop): bool
    {
        $configs = $this->configRepository->findBy(['salesChannelId' => [null, $salesChannelId], 'shop' => $shop]);

        foreach ($configs as $config) {
            $configs[$config->getSalesChannelId()] = $config;
        }

        if (isset($configs[$salesChannelId]) && $configs[$salesChannelId]->isThreeDSecureEnforced() !== null) {
            return $configs[$salesChannelId]->isThreeDSecureEnforced();
        }

        if (isset($configs[null]) && $configs[null]->isThreeDSecureEnforced() !== null) {
            return $configs[null]->isThreeDSecureEnforced();
        }

        return false;
    }
}
