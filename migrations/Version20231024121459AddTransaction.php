<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231024121459AddTransaction extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add transaction table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `transaction` (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', shop_id VARCHAR(255) NOT NULL, braintree_transaction_id VARCHAR(64) NOT NULL, order_transaction_id VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_723705D14D16C4DD (shop_id), UNIQUE INDEX uniq_braintree_transaction_id_order_transaction_id (braintree_transaction_id, order_transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `transaction` ADD CONSTRAINT FK_723705D14D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (shop_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `transaction` DROP FOREIGN KEY FK_723705D14D16C4DD');
        $this->addSql('DROP TABLE `transaction`');
    }
}
