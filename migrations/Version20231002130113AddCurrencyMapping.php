<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231002130113AddCurrencyMapping extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add currency mapping table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE currency_mapping (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', shop_id VARCHAR(255) NOT NULL, currency_id VARCHAR(255) NOT NULL, currency_iso VARCHAR(3) NOT NULL, merchant_account_id VARCHAR(32) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, sales_channel_id VARCHAR(255) DEFAULT NULL, INDEX IDX_944FD6654D16C4DD (shop_id), UNIQUE INDEX uniq_config_id (shop_id, sales_channel_id, merchant_account_id, currency_iso), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE currency_mapping ADD CONSTRAINT FK_944FD6654D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (shop_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE currency_mapping DROP FOREIGN KEY FK_944FD6654D16C4DD');
        $this->addSql('DROP TABLE currency_mapping');
    }
}
