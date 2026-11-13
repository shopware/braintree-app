<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Controller\AdminController;
use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Framework\Service\HmrService;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment;

#[CoversClass(AdminController::class)]
class AdminControllerTest extends TestCase
{
    private ShopEntity $shop;

    protected function setUp(): void
    {
        $this->shop = new ShopEntity('shop-id', '', 'secret');
    }

    public function testAdminSdk(): void
    {
        $hmrService = $this->createMock(HmrService::class);
        $hmrService
            ->expects(static::once())
            ->method('isHmr')
            ->willReturn(false);

        $twig = $this->createMock(Environment::class);
        $twig
            ->expects(static::once())
            ->method('render')
            ->with('admin-sdk.html.twig', ['hmr' => false]);

        $container = $this->createMock(Container::class);
        $container->expects(static::once())->method('has')->with('twig')->willReturn(true);
        $container->expects(static::once())->method('get')->with('twig')->willReturn($twig);

        $controller = new AdminController($hmrService);
        $controller->setContainer($container);

        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects(static::once())
            ->method('set')
            ->with('shop-id', $this->shop->getShopId());

        $controller->adminSdk($session, $this->shop);
    }

    public function testItSetsCookie(): void
    {
        $twig = static::createStub(Environment::class);
        $container = $this->createMock(Container::class);
        $container->expects(static::once())->method('has')->with('twig')->willReturn(true);
        $container->expects(static::once())->method('get')->with('twig')->willReturn($twig);

        $controller = new AdminController(static::createStub(HmrService::class));
        $controller->setContainer($container);

        $session = new Session(new MockArraySessionStorage());
        $response = $controller->adminSdk($session, $this->shop);

        static::assertCount(1, $response->headers->getCookies());

        $cookie = $response->headers->getCookies()[0];

        static::assertSame(session_name(), $cookie->getName());
        static::assertSame(session_id(), $cookie->getValue());
        static::assertSame(Cookie::SAMESITE_NONE, $cookie->getSameSite());
        static::assertTrue($cookie->isSecure());
        static::assertTrue($cookie->isPartitioned());
    }
}
