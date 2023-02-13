<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230210073623 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset
            ADD asset_file_properties_distributes_in_services JSON NOT NULL DEFAULT (JSON_ARRAY()),
            ADD asset_file_properties_slot_names JSON NOT NULL DEFAULT (JSON_ARRAY()),
            ADD asset_file_properties_from_rss TINYINT(1) NOT NULL DEFAULT 0, 
            ADD asset_file_properties_pixels INT UNSIGNED NOT NULL DEFAULT 0,
            ADD asset_file_properties_shortest_dimension INT UNSIGNED NOT NULL DEFAULT 0;
        ');
        $this->addSql('ALTER TABLE asset 
            CHANGE asset_file_properties_distributes_in_services asset_file_properties_distributes_in_services JSON NOT NULL, 
            CHANGE asset_file_properties_slot_names asset_file_properties_slot_names JSON NOT NULL, 
            CHANGE asset_file_properties_from_rss asset_file_properties_from_rss TINYINT(1) NOT NULL, 
            CHANGE asset_file_properties_pixels asset_file_properties_pixels INT UNSIGNED NOT NULL,
            CHANGE asset_file_properties_shortest_dimension asset_file_properties_shortest_dimension INT UNSIGNED NOT NULL;
        ');
        $this->addSql('ALTER TABLE podcast_episode ADD flags_from_rss TINYINT(1) NOT NULL DEFAULT 0;');
        $this->addSql('ALTER TABLE podcast_episode CHANGE flags_from_rss flags_from_rss TINYINT(1) NOT NULL;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset DROP asset_file_properties_distributes_in_services, DROP asset_file_properties_slot_names, DROP asset_file_properties_from_rss, DROP asset_file_properties_pixels');
        $this->addSql('ALTER TABLE podcast_episode DROP flags_from_rss');
    }
}
