<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241114141631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE config CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE currency_mapping CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE transaction CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE transaction_report CHANGE transaction_id transaction_id BINARY(16) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE config CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE transaction_report CHANGE transaction_id transaction_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE currency_mapping CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE `transaction` CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
