<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240108134733 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE custom_form_element SET attributes_type = \'integer\' where attributes_type = \'number\'
        ');
        $this->addSql('
            UPDATE custom_form_element SET attributes_readonly = 1 where property = \'mediaApiPaths\' OR property = \'mediaApiIds\'
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            UPDATE custom_form_element SET attributes_type = \'number\' where attributes_type = \'integer\'
        ');
        $this->addSql('
            UPDATE custom_form_element SET attributes_readonly = 0 where property = \'mediaApiPaths\' OR property = \'mediaApiIds\'
        ');
    }
}
