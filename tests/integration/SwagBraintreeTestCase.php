<?php declare(strict_types=1);

namespace Swag\Braintree\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Swag\Braintree\Entity\ShopEntity;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SwagBraintreeTestCase extends KernelTestCase
{
    protected function tearDown(): void
    {
        $conn = static::getEntityManager()->getConnection();
        $tables = $conn->createSchemaManager()->listTableNames();
        /** @phpstan-ignore-next-line - yes `getTableName` will work */
        $migrationTable = static::getContainer()->get('doctrine.migrations.dependency_factory')
            ->getConfiguration()
            ->getMetadataStorageConfiguration()
            ->getTableName();

        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            foreach ($tables as $table) {
                if ($table === $migrationTable) {
                    continue;
                }

                $conn->executeStatement(\sprintf(
                    'TRUNCATE TABLE %s',
                    $conn->quoteIdentifier($table),
                ));
            }
        } finally {
            $conn->executeStatement('SET FOREIGN_KEY_CHECKS=1;');
        }

        parent::tearDown();
    }

    protected static function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine.orm.default_entity_manager');
    }

    protected static function createShop(string $url = 'https://shop.example.com', string $secret = 'definitly-a-secure-secret'): ShopEntity
    {
        $shopId = \bin2hex(\random_bytes(10));

        $shop = new ShopEntity(
            $shopId,
            $url,
            $secret,
        );

        $em = static::getEntityManager();
        $em->persist($shop);
        $em->flush();

        return $shop;
    }
}
