<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230227142503 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD person_first_name VARCHAR(120) NOT NULL, ADD person_last_name VARCHAR(120) NOT NULL, ADD person_full_name VARCHAR(242) NOT NULL, ADD avatar_color VARCHAR(7) NOT NULL, ADD avatar_text VARCHAR(3) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP person_first_name, DROP person_last_name, DROP person_full_name, DROP avatar_color, DROP avatar_text');
    }
}
