<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Application\Controller;

use Swag\Braintree\Tests\Contract\ApplicationHelperTrait;
use Swag\Braintree\Tests\Ids;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntityControllerTest extends WebTestCase
{
    use ApplicationHelperTrait;

    public function testGetShopEntity(): void
    {
        $shop = static::createShop();
        $client = static::createClientForShop($shop);
        $client->request(Request::METHOD_GET, '/api/entity/shop');

        static::assertResponseStatusCodeSame(Response::HTTP_OK);

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertJson($response->getContent());

        $shop = \json_decode($response->getContent(), true, flags: \JSON_THROW_ON_ERROR);

        static::assertArrayHasKey('shopId', $shop);
        static::assertArrayHasKey('shopUrl', $shop);
        static::assertArrayHasKey('shopSecret', $shop);

        static::assertSame(Ids::get('shop'), $shop['shopId']);
        static::assertSame('https://shop.example.com', $shop['shopUrl']);
        static::assertSame(Ids::get('shop-secret'), $shop['shopSecret']);
    }

    public function testGetShopEntityWithNonExistentShop(): void
    {
        // shop is not persisted: session will not be able to resolve the shop
        $shop = static::createShop(persist: false);

        $client = static::createClientForShop($shop);
        $client->request(Request::METHOD_GET, '/api/entity/shop');

        static::assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
