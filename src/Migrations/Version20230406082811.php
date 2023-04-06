<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230406082811 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE distribution CHANGE publish_at publish_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE podcast_episode CHANGE dates_publication_date dates_publication_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE distribution CHANGE publish_at publish_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE podcast_episode CHANGE dates_publication_date dates_publication_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
    }
}
