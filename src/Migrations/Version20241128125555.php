<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241128125555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_image_copy_item DROP FOREIGN KEY FK_FD6BA1D946FA2F93');
        $this->addSql('ALTER TABLE job_image_copy_item DROP FOREIGN KEY FK_FD6BA1D9DF71C928');
        $this->addSql('DROP INDEX IDX_FD6BA1D946FA2F93 ON job_image_copy_item');
        $this->addSql('DROP INDEX IDX_FD6BA1D9DF71C928 ON job_image_copy_item');
        $this->addSql('ALTER TABLE job_image_copy_item CHANGE source_asset_id source_asset_id VARCHAR(36) NOT NULL, CHANGE target_asset_id target_asset_id VARCHAR(36) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_image_copy_item CHANGE source_asset_id source_asset_id CHAR(36) DEFAULT NULL, CHANGE target_asset_id target_asset_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE job_image_copy_item ADD CONSTRAINT FK_FD6BA1D946FA2F93 FOREIGN KEY (source_asset_id) REFERENCES asset (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE job_image_copy_item ADD CONSTRAINT FK_FD6BA1D9DF71C928 FOREIGN KEY (target_asset_id) REFERENCES asset (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_FD6BA1D946FA2F93 ON job_image_copy_item (source_asset_id)');
        $this->addSql('CREATE INDEX IDX_FD6BA1D9DF71C928 ON job_image_copy_item (target_asset_id)');
    }
}
