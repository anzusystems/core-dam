<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221014112916 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset ADD notify_to_id INT DEFAULT NULL, DROP asset_flags_with_processed_file');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5C12433204 FOREIGN KEY (notify_to_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2AF5A5C12433204 ON asset (notify_to_id)');
        $this->addSql('ALTER TABLE asset_file_metadata ADD keyword_suggestions JSON NOT NULL, ADD author_suggestions JSON NOT NULL');
        $this->addSql('ALTER TABLE author ADD ext_system_id INT DEFAULT NULL, ADD name VARCHAR(255) NOT NULL, ADD type VARCHAR(255) NOT NULL, ADD flags_reviewed TINYINT(1) NOT NULL, DROP first_name, DROP last_name, DROP full_name');
        $this->addSql('ALTER TABLE author ADD CONSTRAINT FK_BDAFD8C8E961F7A FOREIGN KEY (ext_system_id) REFERENCES ext_system (id)');
        $this->addSql('CREATE INDEX IDX_BDAFD8C8E961F7A ON author (ext_system_id)');
        $this->addSql('ALTER TABLE image_file CHANGE image_attributes_most_dominant_color image_attributes_most_dominant_color VARCHAR(255) NOT NULL COMMENT \'(DC2Type:ColorType)\'');
        $this->addSql('DROP INDEX UNIQ_name ON keyword');
        $this->addSql('ALTER TABLE keyword ADD ext_system_id INT DEFAULT NULL, ADD flags_reviewed TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE keyword ADD CONSTRAINT FK_5A93713BE961F7A FOREIGN KEY (ext_system_id) REFERENCES ext_system (id)');
        $this->addSql('CREATE INDEX IDX_5A93713BE961F7A ON keyword (ext_system_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_name_extSystem ON keyword (name, ext_system_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_2AF5A5C12433204');
        $this->addSql('DROP INDEX IDX_2AF5A5C12433204 ON asset');
        $this->addSql('ALTER TABLE asset ADD asset_flags_with_processed_file TINYINT(1) NOT NULL, DROP notify_to_id');
        $this->addSql('ALTER TABLE asset_file_metadata DROP keyword_suggestions, DROP author_suggestions');
        $this->addSql('ALTER TABLE author DROP FOREIGN KEY FK_BDAFD8C8E961F7A');
        $this->addSql('DROP INDEX IDX_BDAFD8C8E961F7A ON author');
        $this->addSql('ALTER TABLE author ADD first_name VARCHAR(120) NOT NULL, ADD last_name VARCHAR(120) NOT NULL, ADD full_name VARCHAR(240) NOT NULL, DROP ext_system_id, DROP name, DROP type, DROP flags_reviewed');
        $this->addSql('ALTER TABLE image_file CHANGE image_attributes_most_dominant_color image_attributes_most_dominant_color VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE keyword DROP FOREIGN KEY FK_5A93713BE961F7A');
        $this->addSql('DROP INDEX IDX_5A93713BE961F7A ON keyword');
        $this->addSql('DROP INDEX UNIQ_name_extSystem ON keyword');
        $this->addSql('ALTER TABLE keyword DROP ext_system_id, DROP flags_reviewed');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_name ON keyword (name)');
    }
}
