<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Integration;

use Swag\Braintree\Entity\ConfigEntity;

class DoctrineUuidTest extends SwagBraintreeTestCase
{
    public function testPersistTwice(): void
    {
        $em = $this->getEntityManager();

        $shop = $this->createShop();
        $config = (new ConfigEntity())
            ->setShop($shop);

        $em->persist($config);
        $em->flush();

        static::assertNotNull($config->getId());
        $id = (string) $config->getId();

        $em->persist($config);
        $em->flush();

        static::assertSame($id, (string) $config->getId());
    }

    public function testWithRef(): void
    {
        $em = $this->getEntityManager();

        $shop = $this->createShop();
        $config = (new ConfigEntity())
            ->setShop($shop);

        $em->persist($config);
        $em->flush();

        static::assertNotNull($config->getId());
        $id = (string) $config->getId();

        $configRef = $em->getReference(ConfigEntity::class, $id);
        $configRef->setThreeDSecureEnforced(true);

        $em->persist($configRef);
        $em->flush();

        $config = $em->find(ConfigEntity::class, $id);

        static::assertSame($id, (string) $config->getId());
        static::assertTrue($config->isThreeDSecureEnforced());
    }
}
