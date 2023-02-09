<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230209184911 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE distribution ADD texts_episode_id VARCHAR(36) DEFAULT NULL, DROP rss_url');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE distribution ADD rss_url VARCHAR(2048) DEFAULT NULL, DROP texts_episode_id');
    }
}
