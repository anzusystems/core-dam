<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260226153855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add internal marking rules to AssetLicence and internal flags to AssetFileFlags';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE asset_licence_internal_rule_author (asset_licence_id INT NOT NULL, author_id CHAR(36) NOT NULL, INDEX IDX_D2EC290BE00EE493 (asset_licence_id), INDEX IDX_D2EC290BF675F31B (author_id), PRIMARY KEY (asset_licence_id, author_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE asset_licence_internal_rule_user (asset_licence_id INT NOT NULL, dam_user_id INT NOT NULL, INDEX IDX_B2C0485BE00EE493 (asset_licence_id), INDEX IDX_B2C0485BAE87B06A (dam_user_id), PRIMARY KEY (asset_licence_id, dam_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE asset_licence_internal_rule_author ADD CONSTRAINT FK_D2EC290BE00EE493 FOREIGN KEY (asset_licence_id) REFERENCES asset_licence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE asset_licence_internal_rule_author ADD CONSTRAINT FK_D2EC290BF675F31B FOREIGN KEY (author_id) REFERENCES author (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE asset_licence_internal_rule_user ADD CONSTRAINT FK_B2C0485BE00EE493 FOREIGN KEY (asset_licence_id) REFERENCES asset_licence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE asset_licence_internal_rule_user ADD CONSTRAINT FK_B2C0485BAE87B06A FOREIGN KEY (dam_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE asset_licence ADD internal_rule_active TINYINT(1) DEFAULT 0 NOT NULL, ADD internal_rule_mark_as_internal_since DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE asset_file ADD flags_internal TINYINT(1) DEFAULT 1 NOT NULL, ADD flags_override_internal TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asset_licence_internal_rule_author DROP FOREIGN KEY FK_D2EC290BE00EE493');
        $this->addSql('ALTER TABLE asset_licence_internal_rule_author DROP FOREIGN KEY FK_D2EC290BF675F31B');
        $this->addSql('ALTER TABLE asset_licence_internal_rule_user DROP FOREIGN KEY FK_B2C0485BE00EE493');
        $this->addSql('ALTER TABLE asset_licence_internal_rule_user DROP FOREIGN KEY FK_B2C0485BAE87B06A');
        $this->addSql('DROP TABLE asset_licence_internal_rule_author');
        $this->addSql('DROP TABLE asset_licence_internal_rule_user');
        $this->addSql('ALTER TABLE asset_licence DROP internal_rule_active, DROP internal_rule_mark_as_internal_since');
        $this->addSql('ALTER TABLE asset_file DROP flags_internal, DROP flags_override_internal');
    }
}
