<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Contract;

use Swag\Braintree\Entity\ShopEntity;
use Swag\Braintree\Tests\Ids;

/**
 * @infection-ignore-all - this is static data
 */
trait ShopHelperTrait
{
    protected static function createShop(): ShopEntity
    {
        return new ShopEntity(
            Ids::get('shop'),
            'https://shop.example.com',
            Ids::get('shop-secret'),
        );
    }
}
