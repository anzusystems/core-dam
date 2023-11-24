<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231120151902 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_form_element CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE distribution_category_option CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE podcast_episode CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE region_of_interest CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE video_show_episode CHANGE position position INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_form_element CHANGE position position SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE distribution_category_option CHANGE position position SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE podcast_episode CHANGE position position SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE region_of_interest CHANGE position position SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE video_show_episode CHANGE position position SMALLINT NOT NULL');
    }
}
