<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231002072740AddConfig extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add config table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE config (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', shop_id VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, sales_channel_id VARCHAR(255) DEFAULT NULL, three_dsecure_enforced TINYINT(1) DEFAULT NULL, INDEX IDX_D48A2F7C4D16C4DD (shop_id), UNIQUE INDEX uniq_sales_channel_id_shop_id (sales_channel_id, shop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE config ADD CONSTRAINT FK_D48A2F7C4D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (shop_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE currency_mapping DROP FOREIGN KEY FK_D48A2F7C4D16C4DD');
        $this->addSql('DROP TABLE config');
    }
}
