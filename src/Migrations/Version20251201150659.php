<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251201150659 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE UNIQUE INDEX UNIQ_podcast_export_type_device_type ON podcast_export_data (podcast_id, export_type, device_type)'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_podcast_export_type_device_type ON podcast_export_data');
    }
}
