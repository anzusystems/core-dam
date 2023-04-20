<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230420093734 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE distribution CHANGE texts_author texts_author VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE podcast CHANGE texts_title texts_title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE podcast_episode CHANGE attributes_rss_id attributes_rss_id VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE distribution CHANGE texts_author texts_author VARCHAR(256) DEFAULT NULL');
        $this->addSql('ALTER TABLE podcast CHANGE texts_title texts_title VARCHAR(128) NOT NULL');
        $this->addSql('ALTER TABLE podcast_episode CHANGE attributes_rss_id attributes_rss_id VARCHAR(256) NOT NULL');
    }
}
