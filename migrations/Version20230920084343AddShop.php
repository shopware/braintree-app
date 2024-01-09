<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230920084343AddShop extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add shop';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE shop (shop_id VARCHAR(255) NOT NULL, braintree_public_key VARCHAR(255) DEFAULT NULL, braintree_private_key VARCHAR(255) DEFAULT NULL, braintree_merchant_id VARCHAR(255) DEFAULT NULL, shop_url VARCHAR(255) NOT NULL, shop_secret VARCHAR(255) NOT NULL, shop_client_id VARCHAR(255) DEFAULT NULL, shop_client_secret VARCHAR(255) DEFAULT NULL, shop_active TINYINT(1) NOT NULL, braintree_sandbox TINYINT(1) NOT NULL DEFAULT 0, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(shop_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE shop');
    }
}
