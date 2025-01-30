<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250117082114 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE podcast_episode
                ADD attributes_web_order_position INT UNSIGNED DEFAULT 0 NOT NULL,
                ADD attributes_mobile_order_position INT UNSIGNED DEFAULT 0 NOT NULL,
                ADD attributes_duration INT UNSIGNED DEFAULT 0 NOT NULL,
                ADD flags_web_public_export_enabled TINYINT(1) DEFAULT 0 NOT NULL,
                ADD flags_mobile_public_export_enabled TINYINT(1) DEFAULT 0 NOT NULL
        ');
        $this->addSql('
            ALTER TABLE podcast 
                ADD attributes_web_order_position INT UNSIGNED DEFAULT 0 NOT NULL, 
                ADD attributes_mobile_order_position INT UNSIGNED DEFAULT 0 NOT NULL, 
                ADD flags_web_public_export_enabled TINYINT(1) DEFAULT 0 NOT NULL, 
                ADD flags_mobile_public_export_enabled TINYINT(1) DEFAULT 0 NOT NULL
        ');
        $this->addSql('
            CREATE TABLE public_export (
                id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                slug VARCHAR(255) NOT NULL,
                type VARCHAR(255) NOT NULL, 
                ext_system_id INT DEFAULT NULL, 
                asset_licence_id INT DEFAULT NULL, 
                created_at DATETIME NOT NULL, 
                modified_at DATETIME NOT NULL, 
                created_by_id INT DEFAULT NULL, 
                modified_by_id INT DEFAULT NULL, 
                INDEX IDX_427DB92BE961F7A (ext_system_id), 
                INDEX IDX_427DB92BE00EE493 (asset_licence_id), 
                INDEX IDX_427DB92BB03A8386 (created_by_id), 
                INDEX IDX_427DB92B99049ECE (modified_by_id), 
                UNIQUE INDEX UNIQ_slug (slug), 
                PRIMARY KEY(id)
           ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE public_export ADD CONSTRAINT FK_427DB92BE961F7A FOREIGN KEY (ext_system_id) REFERENCES ext_system (id)');
        $this->addSql('ALTER TABLE public_export ADD CONSTRAINT FK_427DB92BE00EE493 FOREIGN KEY (asset_licence_id) REFERENCES asset_licence (id)');
        $this->addSql('ALTER TABLE public_export ADD CONSTRAINT FK_427DB92BB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE public_export ADD CONSTRAINT FK_427DB92B99049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');

        $this->addSql('
            ALTER TABLE podcast_episode 
                ADD licence_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE podcast_episode 
                ADD CONSTRAINT FK_77EB2BD026EF07C9 FOREIGN KEY (licence_id) REFERENCES asset_licence (id)
        ');
        $this->addSql('
            CREATE INDEX IDX_77EB2BD026EF07C9 ON podcast_episode (licence_id)
        ');
        $this->addSql('
            ALTER TABLE video_show 
                ADD flags_web_public_export_enabled TINYINT(1) DEFAULT 0 NOT NULL,
                ADD flags_mobile_public_export_enabled TINYINT(1) DEFAULT 0 NOT NULL, 
                ADD attributes_web_order_position INT UNSIGNED DEFAULT 0 NOT NULL, 
                ADD attributes_mobile_order_position INT UNSIGNED DEFAULT 0 NOT NULL
        ');

        $this->addSql('
            ALTER TABLE video_show_episode 
                ADD dates_publication_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(), 
                ADD flags_web_public_export_enabled TINYINT(1) DEFAULT 0 NOT NULL,
                ADD flags_mobile_public_export_enabled TINYINT(1) DEFAULT 0 NOT NULL, 
                ADD attributes_web_order_position INT UNSIGNED DEFAULT 0 NOT NULL, 
                ADD attributes_mobile_order_position INT UNSIGNED DEFAULT 0 NOT NULL
        ');

        $this->addSql('ALTER TABLE author ADD flags_can_be_current_author TINYINT(1) DEFAULT 1 NOT NULL');

        $this->addSql('
            CREATE INDEX IDX_show_publication_mobile_ordering ON video_show_episode (attributes_mobile_order_position DESC,
                                                                            asset_id,
                                                                            video_show_id,
                                                                            flags_mobile_public_export_enabled,
                                                                            dates_publication_date)
        ');
        $this->addSql('
            CREATE INDEX IDX_show_publication_web_ordering ON video_show_episode (attributes_web_order_position DESC,
                                                                         asset_id,
                                                                         video_show_id,
                                                                         flags_web_public_export_enabled,
                                                                         dates_publication_date)
        ');
        $this->addSql('
            CREATE INDEX IDX_licence_web_ordering ON video_show (attributes_web_order_position, licence_id, 
                                                                 flags_web_public_export_enabled)
        ');
        $this->addSql('
            CREATE INDEX IDX_licence_mobile_ordering ON video_show (attributes_mobile_order_position, licence_id,
                                                                    flags_mobile_public_export_enabled)
        ');
        $this->addSql('
            CREATE INDEX IDX_licence_web_ordering ON podcast (attributes_web_order_position, licence_id, 
                                                              flags_web_public_export_enabled)
        ');
        $this->addSql('
            CREATE INDEX IDX_licence_mobile_ordering ON podcast (attributes_mobile_order_position, licence_id,
                                                                flags_mobile_public_export_enabled)
        ');
        $this->addSql('
            CREATE INDEX IDX_podcast_web_ordering ON podcast_episode (attributes_web_order_position DESC,
                                                          asset_id, podcast_id, flags_web_public_export_enabled)
        ');
        $this->addSql('
            CREATE INDEX IDX_podcast_mobile_ordering ON podcast_episode (attributes_mobile_order_position DESC,
                                                             asset_id, podcast_id, flags_mobile_public_export_enabled)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE podcast_episode 
                DROP attributes_web_order_position, 
                DROP attributes_mobile_order_position, 
                DROP flags_web_public_export_enabled, 
                DROP flags_mobile_public_export_enabled
        ');
        $this->addSql('ALTER TABLE public_export DROP FOREIGN KEY FK_427DB92BE961F7A');
        $this->addSql('ALTER TABLE public_export DROP FOREIGN KEY FK_427DB92BE00EE493');
        $this->addSql('ALTER TABLE public_export DROP FOREIGN KEY FK_427DB92BB03A8386');
        $this->addSql('ALTER TABLE public_export DROP FOREIGN KEY FK_427DB92B99049ECE');
        $this->addSql('DROP TABLE public_export');
        $this->addSql('ALTER TABLE podcast_episode DROP FOREIGN KEY FK_77EB2BD026EF07C9');
        $this->addSql('DROP INDEX IDX_77EB2BD026EF07C9 ON podcast_episode');
        $this->addSql('ALTER TABLE podcast_episode DROP licence_id');
        $this->addSql('DROP INDEX IDX_licence_web_ordering ON podcast');
        $this->addSql('DROP INDEX IDX_licence_mobile_ordering ON podcast');
        $this->addSql('DROP INDEX IDX_licence_web_ordering ON podcast_episode');
        $this->addSql('DROP INDEX IDX_licence_mobile_ordering ON podcast_episode');
        $this->addSql('DROP INDEX IDX_licence_web_ordering ON video_show');
        $this->addSql('DROP INDEX IDX_licence_mobile_ordering ON video_show');
        $this->addSql('ALTER TABLE video_show DROP flags_web_public_export_enabled, DROP flags_mobile_public_export_enabled, DROP attributes_web_order_position, DROP attributes_mobile_order_position');
        $this->addSql('DROP INDEX IDX_licence_web_ordering ON video_show_episode');
        $this->addSql('DROP INDEX IDX_licence_mobile_ordering ON video_show_episode');
        $this->addSql('ALTER TABLE video_show_episode DROP dates_publication_date, DROP flags_web_public_export_enabled, DROP flags_mobile_public_export_enabled, DROP attributes_web_order_position, DROP attributes_mobile_order_position');
        $this->addSql('ALTER TABLE author DROP flags_can_be_current_author');
    }
}
