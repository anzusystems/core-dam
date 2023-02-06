<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230206073918 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE distribution ADD texts_ext_rss_id VARCHAR(256) DEFAULT NULL, ADD texts_free_url VARCHAR(2048) DEFAULT NULL, ADD texts_premium_url VARCHAR(2048) DEFAULT NULL, ADD texts_rubric_id INT DEFAULT NULL, ADD texts_podcast_id VARCHAR(36) DEFAULT NULL, ADD flags_create_article TINYINT(1) DEFAULT NULL, DROP custom_data');
        $this->addSql('ALTER TABLE podcast_episode ADD attributes_rss_url VARCHAR(2048) NOT NULL, CHANGE attributes_ext_id attributes_rss_id VARCHAR(256) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE distribution ADD custom_data JSON DEFAULT NULL, DROP texts_ext_rss_id, DROP texts_free_url, DROP texts_premium_url, DROP texts_rubric_id, DROP texts_podcast_id, DROP flags_create_article');
        $this->addSql('ALTER TABLE podcast_episode DROP attributes_rss_url, CHANGE attributes_rss_id attributes_ext_id VARCHAR(256) NOT NULL');
    }
}
