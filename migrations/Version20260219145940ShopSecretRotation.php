<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260219145940ShopSecretRotation extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE shop ADD pending_shop_url VARCHAR(255) DEFAULT NULL, ADD pending_shop_secret VARCHAR(255) DEFAULT NULL, ADD previous_shop_secret VARCHAR(255) DEFAULT NULL, ADD secrets_rotated_at DATETIME DEFAULT NULL, ADD has_verified_with_double_signature TINYINT NOT NULL, ADD is_registration_confirmed TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE shop DROP pending_shop_url, DROP pending_shop_secret, DROP previous_shop_secret, DROP secrets_rotated_at, DROP has_verified_with_double_signature, DROP is_registration_confirmed');
    }
}
