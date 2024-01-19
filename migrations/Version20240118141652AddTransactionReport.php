<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240118141652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transaction_report (transaction_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', currency_iso VARCHAR(3) NOT NULL, total_price NUMERIC(20, 2) NOT NULL, PRIMARY KEY(transaction_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transaction_report ADD CONSTRAINT FK_B25205C42FC0CB0F FOREIGN KEY (transaction_id) REFERENCES `transaction` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction_report DROP FOREIGN KEY FK_B25205C42FC0CB0F');
        $this->addSql('DROP TABLE transaction_report');
    }
}
