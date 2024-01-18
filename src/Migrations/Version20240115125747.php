<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240115125747 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audio_file
            CHANGE audio_public_link_path audio_public_link_path VARCHAR(255) DEFAULT \'\',
            CHANGE audio_public_link_slug audio_public_link_slug VARCHAR(128) DEFAULT \'\',
            CHANGE audio_public_link_is_public audio_public_link_is_public TINYINT(1) DEFAULT 0
        ');
        //        $this->addSql('ALTER TABLE audio_file DROP audio_public_link_path, DROP audio_public_link_slug, DROP audio_public_link_is_public');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audio_file ADD audio_public_link_path VARCHAR(255) NOT NULL, ADD audio_public_link_slug VARCHAR(128) NOT NULL, ADD audio_public_link_is_public TINYINT(1) NOT NULL');
    }
}
