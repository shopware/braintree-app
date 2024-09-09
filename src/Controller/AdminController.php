<?php declare(strict_types=1);

namespace Swag\Braintree\Controller;

use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Framework\Request\ShopResolver;
use Swag\Braintree\Framework\Service\HmrService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly HmrService $hmrService,
    ) {
    }

    #[Route(
        path: '/admin-sdk',
        name: 'swag.braintree.admin-sdk',
        methods: [Request::METHOD_GET]
    )]
    public function adminSdk(SessionInterface $session, ShopEntity $shop): Response
    {
        $session->set(ShopResolver::SHOP_ID, $shop->getShopId());

        $cookie = Cookie::create(\session_name())
            ->withValue(\session_id())
            ->withSameSite(Cookie::SAMESITE_NONE)
            ->withSecure()
            ->withPartitioned();

        $response = $this->render(
            'admin-sdk.html.twig',
            ['hmr' => $this->hmrService->isHmr()]
        );
        $response->headers->setCookie($cookie);

        return $response;
    }
}
