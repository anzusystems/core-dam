<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221207113255 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE podcast (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', licence_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', texts_title VARCHAR(128) NOT NULL, texts_description VARCHAR(2000) NOT NULL, attributes_rss_url VARCHAR(2048) DEFAULT NULL, attributes_file_position VARCHAR(128) DEFAULT NULL, attributes_last_import_status VARCHAR(255) NOT NULL, attributes_mode VARCHAR(255) NOT NULL, INDEX IDX_D7E805BD26EF07C9 (licence_id), INDEX IDX_D7E805BDB03A8386 (created_by_id), INDEX IDX_D7E805BD99049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE podcast_episode (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', podcast_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', position SMALLINT NOT NULL, dates_publication_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', attributes_ext_id VARCHAR(256) NOT NULL, attributes_season_number SMALLINT UNSIGNED DEFAULT NULL, attributes_episode_number SMALLINT UNSIGNED DEFAULT NULL, texts_title VARCHAR(255) NOT NULL, texts_description VARCHAR(2000) NOT NULL, texts_raw_description LONGTEXT NOT NULL, INDEX IDX_77EB2BD0786136AB (podcast_id), INDEX IDX_77EB2BD05DA1941 (asset_id), INDEX IDX_77EB2BD0B03A8386 (created_by_id), INDEX IDX_77EB2BD099049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BD26EF07C9 FOREIGN KEY (licence_id) REFERENCES asset_licence (id)');
        $this->addSql('ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BDB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BD99049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE podcast_episode ADD CONSTRAINT FK_77EB2BD0786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id)');
        $this->addSql('ALTER TABLE podcast_episode ADD CONSTRAINT FK_77EB2BD05DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id)');
        $this->addSql('ALTER TABLE podcast_episode ADD CONSTRAINT FK_77EB2BD0B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE podcast_episode ADD CONSTRAINT FK_77EB2BD099049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset DROP texts_description');
        $this->addSql('CREATE INDEX IDX_texts_title ON asset (texts_title)');
        $this->addSql('ALTER TABLE asset_file ADD asset_attributes_create_strategy VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE chunk DROP created_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BD26EF07C9');
        $this->addSql('ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BDB03A8386');
        $this->addSql('ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BD99049ECE');
        $this->addSql('ALTER TABLE podcast_episode DROP FOREIGN KEY FK_77EB2BD0786136AB');
        $this->addSql('ALTER TABLE podcast_episode DROP FOREIGN KEY FK_77EB2BD05DA1941');
        $this->addSql('ALTER TABLE podcast_episode DROP FOREIGN KEY FK_77EB2BD0B03A8386');
        $this->addSql('ALTER TABLE podcast_episode DROP FOREIGN KEY FK_77EB2BD099049ECE');
        $this->addSql('DROP TABLE podcast');
        $this->addSql('DROP TABLE podcast_episode');
        $this->addSql('DROP INDEX IDX_texts_title ON asset');
        $this->addSql('ALTER TABLE asset ADD texts_description VARCHAR(5000) NOT NULL');
        $this->addSql('ALTER TABLE asset_file DROP asset_attributes_create_strategy');
        $this->addSql('ALTER TABLE chunk ADD created_at DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\'');
    }
}
