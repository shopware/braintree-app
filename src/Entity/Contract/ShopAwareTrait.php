<?php declare(strict_types=1);

namespace Swag\Braintree\Entity\Contract;

use Doctrine\ORM\Mapping as ORM;
use Shopware\App\SDK\Shop\ShopInterface;
use Swag\Braintree\Entity\ShopEntity;

#[ORM\MappedSuperclass]
trait ShopAwareTrait
{
    #[ORM\ManyToOne(targetEntity: ShopEntity::class)]
    #[ORM\JoinColumn(name: 'shop_id', referencedColumnName: 'shop_id', nullable: false)]
    private ShopInterface $shop;

    public function getShop(): ShopInterface
    {
        return $this->shop;
    }

    public function setShop(ShopInterface $shop): self
    {
        $this->shop = $shop;

        return $this;
    }
}
