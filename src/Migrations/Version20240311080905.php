<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240311080905 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE podcast 
                ADD attributes_ext_url VARCHAR(2048) DEFAULT \'\' NOT NULL
            ');
        $this->addSql('
            ALTER TABLE podcast_episode 
                ADD attributes_ext_url VARCHAR(2048) DEFAULT \'\' NOT NULL
        ');
        $this->addSql('
            ALTER TABLE distribution 
                ADD texts_bonus_url VARCHAR(2048) DEFAULT \'\'
        ');
        $this->addSql('
            ALTER TABLE distribution 
                ADD attributes_bonus_duration INT UNSIGNED DEFAULT 0
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE podcast DROP attributes_ext_url');
        $this->addSql('ALTER TABLE podcast_episode DROP attributes_ext_url');
        $this->addSql('ALTER TABLE distribution DROP texts_bonus_url');
        $this->addSql('ALTER TABLE distribution DROP attributes_bonus_duration');
    }
}
