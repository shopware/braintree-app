<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Contract;

use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Framework\Request\ShopResolver;
use Symfony\Bundle\FrameworkBundle\Test\BrowserKitAssertionsTrait;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

trait ApplicationHelperTrait
{
    use BrowserKitAssertionsTrait;
    use IntegrationHelperTrait;

    /**
     * @return AbstractBrowser<Request, Response>
     */
    protected static function createClientForShop(?ShopInterface $shop = null): AbstractBrowser
    {
        $client = static::getContainer()->get('test.client');

        if (!$shop) {
            $shop = static::createShop(persist: false);
        }

        /** @var SessionInterface $session */
        $session = $client->getContainer()->get('session.factory')->createSession();
        $session->set(ShopResolver::SHOP_ID, $shop->getShopId());
        $session->save();
        $session->start();

        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        return static::getClient($client);
    }
}
