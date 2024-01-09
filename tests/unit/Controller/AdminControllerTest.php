<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Unit\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Swag\Braintree\Controller\AdminController;
use Swag\Braintree\Entity\ShopEntity;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

#[CoversClass(AdminController::class)]
class AdminControllerTest extends TestCase
{
    private AdminController $controller;

    private ShopEntity $shop;

    protected function setUp(): void
    {
        $this->controller = new AdminController();
        $this->shop = new ShopEntity('shop-id', '', 'secret');
    }

    public function testAdminSdk(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig
            ->expects(static::once())
            ->method('render')
            ->with('admin-sdk.html.twig');

        $container = $this->createMock(Container::class);
        $container->method('has')->with('twig')->willReturn(true);
        $container->method('get')->with('twig')->willReturn($twig);

        $this->controller->setContainer($container);

        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects(static::once())
            ->method('set')
            ->with('shop-id', $this->shop->getShopId());

        $this->controller->adminSdk($session, $this->shop);
    }
}
