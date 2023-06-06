<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230511131450 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_status_created_auto_delete ON asset (attributes_status, created_at, asset_flags_auto_delete_unprocessed)');
        $this->addSql('ALTER TABLE podcast ADD alt_image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BD9822B3B6 FOREIGN KEY (alt_image_id) REFERENCES image_preview (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D7E805BD9822B3B6 ON podcast (alt_image_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_status_created_auto_delete ON asset');
        $this->addSql('ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BD9822B3B6');
        $this->addSql('DROP INDEX UNIQ_D7E805BD9822B3B6 ON podcast');
        $this->addSql('ALTER TABLE podcast DROP alt_image_id');
    }
}
