<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230217083733 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE podcast_episode ADD attributes_last_import_status VARCHAR(255) NOT NULL DEFAULT \'not_imported\'');
        $this->addSql('ALTER TABLE podcast_episode CHANGE attributes_last_import_status attributes_last_import_status VARCHAR(255) NOT NULL;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE podcast_episode DROP attributes_last_import_status');
    }
}
