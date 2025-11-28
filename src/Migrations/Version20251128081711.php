<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251128081711 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE podcast_export_data (export_type VARCHAR(255) NOT NULL, device_type VARCHAR(255) NOT NULL, body JSON NOT NULL, id INT UNSIGNED AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, podcast_id CHAR(36) NOT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, INDEX IDX_1ABAE24B786136AB (podcast_id), INDEX IDX_1ABAE24BB03A8386 (created_by_id), INDEX IDX_1ABAE24B99049ECE (modified_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE podcast_export_data ADD CONSTRAINT FK_1ABAE24B786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE podcast_export_data ADD CONSTRAINT FK_1ABAE24BB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE podcast_export_data ADD CONSTRAINT FK_1ABAE24B99049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job CHANGE last_batch_processed_record last_batch_processed_record VARCHAR(2000) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE podcast_export_data DROP FOREIGN KEY FK_1ABAE24B786136AB');
        $this->addSql('ALTER TABLE podcast_export_data DROP FOREIGN KEY FK_1ABAE24BB03A8386');
        $this->addSql('ALTER TABLE podcast_export_data DROP FOREIGN KEY FK_1ABAE24B99049ECE');
        $this->addSql('DROP TABLE podcast_export_data');
        $this->addSql('ALTER TABLE job CHANGE last_batch_processed_record last_batch_processed_record VARCHAR(255) NOT NULL');
    }
}
