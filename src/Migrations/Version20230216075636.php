<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230216075636 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset 
            ADD asset_file_properties_height INT UNSIGNED NOT NULL DEFAULT 0, 
            CHANGE asset_file_properties_shortest_dimension asset_file_properties_width INT UNSIGNED NOT NULL DEFAULT 0,
            DROP asset_file_properties_pixels
        ');
        $this->addSql('ALTER TABLE asset 
            CHANGE asset_file_properties_height asset_file_properties_height INT UNSIGNED NOT NULL,
            CHANGE asset_file_properties_width asset_file_properties_width INT UNSIGNED NOT NULL;
        ');
        $this->addSql('ALTER TABLE job ADD podcast_id VARCHAR(36) DEFAULT NULL, ADD full_sync TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset 
            ADD asset_file_properties_shortest_dimension INT UNSIGNED NOT NULL, 
            DROP asset_file_properties_width, DROP asset_file_properties_height,
            ADD asset_file_properties_pixels INT UNSIGNED NOT NULL
        ');
        $this->addSql('ALTER TABLE job DROP podcast_id, DROP full_sync');
    }
}
