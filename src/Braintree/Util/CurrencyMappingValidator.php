<?php declare(strict_types=1);

namespace Swag\Braintree\Braintree\Util;

use Braintree\MerchantAccount;
use Swag\Braintree\Braintree\Gateway\BraintreeConnectionService;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Repository\CurrencyMappingRepository;

class CurrencyMappingValidator
{
    public function __construct(
        private readonly CurrencyMappingRepository $currencyMappingRepository,
        private readonly BraintreeConnectionService $connectionService,
    ) {
    }

    public function deleteInvalidCurrencyMappings(ShopEntity $shop): void
    {
        $merchantAccountIds = \array_map(
            static fn (MerchantAccount $merchantAccount) => $merchantAccount->id,
            $this->connectionService->fromShop($shop)->getAllMerchantAccounts(),
        );

        $qb = $this->currencyMappingRepository->createQueryBuilder('currencyMapping');
        $qb
            ->delete()
            ->where($qb->expr()->notIn('currencyMapping.merchantAccountId', ':merchantAccountIds'))
            ->andWhere('currencyMapping.shop = :shop')
            ->setParameters(['shop' => $shop, 'merchantAccountIds' => $merchantAccountIds])
            ->getQuery()
            ->execute();
    }
}
