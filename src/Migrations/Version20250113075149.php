<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250113075149 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE distribution d
            INNER JOIN asset ass ON d.asset_id = ass.id
            SET d.ext_system_id = ass.ext_system_id
        ');
    }

    public function down(Schema $schema): void
    {
    }
}
