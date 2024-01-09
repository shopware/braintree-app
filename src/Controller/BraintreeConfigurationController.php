<?php declare(strict_types=1);

namespace Swag\Braintree\Controller;

use Braintree\MerchantAccount;
use Doctrine\ORM\EntityManagerInterface;
use Swag\Braintree\Braintree\Gateway\BraintreeConnectionService;
use Swag\Braintree\Braintree\Gateway\Connection\BraintreeConnectionStatus;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(path: '/api')]
class BraintreeConfigurationController extends AbstractController
{
    public function __construct(
        private readonly BraintreeConnectionService $connectionService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ShopRepository $shopRepository,
    ) {
    }

    /**
     * @return MerchantAccount[]
     */
    #[Route(
        path: '/braintree/merchant_accounts',
        name: 'braintree.merchant_accounts',
        methods: [Request::METHOD_GET]
    )]
    public function getMerchantAccounts(): array
    {
        return $this->connectionService->getAllMerchantAccounts();
    }

    #[Route(
        path: '/braintree/default_merchant_account',
        name: 'braintree.default_merchant_account',
        methods: [Request::METHOD_GET]
    )]
    public function getDefaultMerchantAccount(): ?MerchantAccount
    {
        return $this->connectionService->getDefaultMerchantAccount();
    }

    #[Route(
        path: '/config/status',
        name: 'braintree.config.status',
        methods: [Request::METHOD_GET]
    )]
    public function configStatus(): BraintreeConnectionStatus
    {
        return $this->connectionService->testConnection();
    }

    #[Route(
        path: '/config/test',
        name: 'braintree.config.test',
        methods: [Request::METHOD_POST]
    )]
    public function configTest(Request $request, ShopEntity $shop): BraintreeConnectionStatus
    {
        /** @var ShopEntity $shop */
        $shop = $this->shopRepository->deserializeInto($shop, $request->getContent());

        return $this->connectionService->fromShop($shop)->testConnection();
    }

    #[Route(
        path: '/config',
        name: 'braintree.config.disconnect',
        methods: [Request::METHOD_DELETE]
    )]
    public function configDisconnect(ShopEntity $shop): void
    {
        $shop->reset();

        $this->entityManager->persist($shop);
        $this->entityManager->flush();
    }
}
