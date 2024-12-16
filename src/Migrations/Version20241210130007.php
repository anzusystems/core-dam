<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241210130007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE author_clean_phrase
                (
                    phrase                VARCHAR(255) NOT NULL COLLATE `utf8mb4_bin`,
                    type                  VARCHAR(255) NOT NULL,
                    mode                  VARCHAR(255) NOT NULL,
                    id                    INT UNSIGNED AUTO_INCREMENT NOT NULL,
                    created_at            DATETIME     NOT NULL,
                    modified_at           DATETIME     NOT NULL,
                    position              INT          NOT NULL,
                    flags_word_boundary   TINYINT(1) DEFAULT 0 NOT NULL,
                    ext_system_id         INT      DEFAULT NULL,
                    author_replacement_id CHAR(36) DEFAULT NULL,
                    created_by_id         INT      DEFAULT NULL,
                    modified_by_id        INT      DEFAULT NULL,
                    INDEX                 IDX_5FF642AEE961F7A (ext_system_id),
                    INDEX                 IDX_5FF642AE43AD0CCA (author_replacement_id),
                    INDEX                 IDX_5FF642AEB03A8386 (created_by_id),
                    INDEX                 IDX_5FF642AE99049ECE (modified_by_id),
                    UNIQUE INDEX UNIQ_ext_system_phrase (phrase, ext_system_id),
                    PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`'
        );
        $this->addSql('ALTER TABLE author_clean_phrase ADD CONSTRAINT FK_5FF642AEE961F7A FOREIGN KEY (ext_system_id) REFERENCES ext_system (id)');
        $this->addSql('ALTER TABLE author_clean_phrase ADD CONSTRAINT FK_5FF642AE43AD0CCA FOREIGN KEY (author_replacement_id) REFERENCES author (id)');
        $this->addSql('ALTER TABLE author_clean_phrase ADD CONSTRAINT FK_5FF642AEB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE author_clean_phrase ADD CONSTRAINT FK_5FF642AE99049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE author_clean_phrase DROP FOREIGN KEY FK_5FF642AEE961F7A');
        $this->addSql('ALTER TABLE author_clean_phrase DROP FOREIGN KEY FK_5FF642AE43AD0CCA');
        $this->addSql('ALTER TABLE author_clean_phrase DROP FOREIGN KEY FK_5FF642AEB03A8386');
        $this->addSql('ALTER TABLE author_clean_phrase DROP FOREIGN KEY FK_5FF642AE99049ECE');
        $this->addSql('DROP TABLE author_clean_phrase');
    }
}
