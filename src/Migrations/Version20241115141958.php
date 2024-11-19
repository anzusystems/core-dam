<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241115141958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE job_image_copy
            (
                licence_id INT DEFAULT NULL,
                id         INT NOT NULL,
                INDEX      IDX_D5F7C10126EF07C9 (licence_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`'
        );
        $this->addSql(
            'CREATE TABLE job_image_copy_item
            (
                status          VARCHAR(255)                NOT NULL,
                id              INT UNSIGNED AUTO_INCREMENT NOT NULL,
                source_asset_id CHAR(36) DEFAULT NULL,
                target_asset_id CHAR(36) DEFAULT NULL,
                job_id          INT      DEFAULT NULL,
                INDEX IDX_FD6BA1D946FA2F93 (source_asset_id),
                INDEX IDX_FD6BA1D9DF71C928 (target_asset_id),
                INDEX IDX_FD6BA1D9BE04EA9 (job_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
              COLLATE `utf8mb4_unicode_ci`'
        );
        $this->addSql(
            'CREATE INDEX STATUS_IDX ON job_image_copy_item (status)'
        );
        $this->addSql(
            'ALTER TABLE job_image_copy
                ADD CONSTRAINT FK_D5F7C10126EF07C9 FOREIGN KEY (licence_id) REFERENCES asset_licence (id)'
        );
        $this->addSql(
            'ALTER TABLE job_image_copy
                ADD CONSTRAINT FK_D5F7C101BF396750 FOREIGN KEY (id) REFERENCES job (id) ON DELETE CASCADE'
        );
        $this->addSql(
            'ALTER TABLE job_image_copy_item
                ADD CONSTRAINT FK_FD6BA1D946FA2F93 FOREIGN KEY (source_asset_id) REFERENCES asset (id)'
        );
        $this->addSql(
            'ALTER TABLE job_image_copy_item 
                ADD CONSTRAINT FK_FD6BA1D9DF71C928 FOREIGN KEY (target_asset_id) REFERENCES asset (id)'
        );
        $this->addSql(
            'ALTER TABLE job_image_copy_item 
                ADD CONSTRAINT FK_FD6BA1D9BE04EA9 FOREIGN KEY (job_id) REFERENCES job_image_copy (id)'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX STATUS_IDX ON job_image_copy_item');
        $this->addSql('ALTER TABLE job_image_copy DROP FOREIGN KEY FK_D5F7C10126EF07C9');
        $this->addSql('ALTER TABLE job_image_copy DROP FOREIGN KEY FK_D5F7C101BF396750');
        $this->addSql('ALTER TABLE job_image_copy_item DROP FOREIGN KEY FK_FD6BA1D946FA2F93');
        $this->addSql('ALTER TABLE job_image_copy_item DROP FOREIGN KEY FK_FD6BA1D9DF71C928');
        $this->addSql('ALTER TABLE job_image_copy_item DROP FOREIGN KEY FK_FD6BA1D9BE04EA9');
        $this->addSql('DROP TABLE job_image_copy');
        $this->addSql('DROP TABLE job_image_copy_item');
    }
}
